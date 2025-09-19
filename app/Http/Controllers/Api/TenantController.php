<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTenantRequest;
use App\Http\Requests\Tenant\UpdateTenantRequest;
use App\Http\Resources\TenantResource;
use App\Models\Tenant;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class TenantController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $query = Tenant::query()->with(['user', 'property.landlord']);

        $user = $request->user();

        // Filter by landlord if user is a landlord
        if ($user->isLandlord()) {
            $query->whereHas('property', function ($q) use ($user) {
                $q->where('landlord_id', $user->id);
            });
        }

        // Filter by property
        if ($request->has('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter active leases
        if ($request->has('active_leases') && $request->active_leases) {
            $query->currentLeases();
        }

        // Filter expiring leases
        if ($request->has('expiring_leases') && $request->expiring_leases) {
            $days = $request->get('expiring_days', 30);
            $query->expiringLeases($days);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            })->orWhere('unit_number', 'like', "%{$search}%");
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $tenants = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => TenantResource::collection($tenants),
            'meta' => [
                'current_page' => $tenants->currentPage(),
                'last_page' => $tenants->lastPage(),
                'per_page' => $tenants->perPage(),
                'total' => $tenants->total(),
            ]
        ]);
    }


    public function store(StoreTenantRequest $request)
    {
        $this->authorize('create', Tenant::class);

        // Check if user_id is provided
        if (!$request->user_id) {
            // Create a new user with the role of "Tenant"
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'role' => 'tenant', // Assign the "Tenant" role
                'status' => 'active', // Default status
            ]);

            // Merge the created user's ID into the request
            $request->merge(['user_id' => $user->id]);
        }

        // Ensure user_id is present in the validated data
        $validatedData = $request->validated();
        $validatedData['user_id'] = $request->user_id;

        // Create the tenant record
        $tenant = Tenant::create($validatedData);

        // Decrease available units in property
        $tenant->property->decrementAvailableUnits();

        // Create lease expiry notification if lease is expiring soon
        if ($tenant->isLeaseExpiringSoon()) {
            Notification::createLeaseExpiry($tenant->user_id, $tenant->lease_end);
        }

        return response()->json([
            'success' => true,
            'message' => 'Tenant created successfully',
            'data' => new TenantResource($tenant->load(['user', 'property']))
        ], 201);
    }
    public function show(Tenant $tenant)
    {
        $this->authorize('view', $tenant);

        return response()->json([
            'success' => true,
            'data' => new TenantResource($tenant->load([
                'user',
                'property.landlord',
                'payments' => function ($query) {
                    $query->latest()->take(10);
                },
                'maintenanceRequests' => function ($query) {
                    $query->latest()->take(5);
                }
            ]))
        ]);
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant)
    {
        $this->authorize('update', $tenant);

        $oldStatus = $tenant->status;
        $tenant->update($request->validated());

        // Handle status changes
        if ($oldStatus !== $tenant->status) {
            if ($tenant->status === 'inactive' && $oldStatus === 'active') {
                // Tenant moved out - increase available units
                $tenant->property->incrementAvailableUnits();
            } elseif ($tenant->status === 'active' && $oldStatus === 'inactive') {
                // Tenant moved in - decrease available units
                $tenant->property->decrementAvailableUnits();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Tenant updated successfully',
            'data' => new TenantResource($tenant->load(['user', 'property']))
        ]);
    }

    public function destroy(Tenant $tenant)
    {
        $this->authorize('delete', $tenant);

        // Check if tenant has outstanding payments
        if ($tenant->getOutstandingBalance() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete tenant with outstanding payments'
            ], 422);
        }

        // Increase available units in property
        if ($tenant->status === 'active') {
            $tenant->property->incrementAvailableUnits();
        }

        $tenant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tenant deleted successfully'
        ]);
    }

    public function expiringLeases(Request $request)
    {
        $days = $request->get('days', 30);
        $query = Tenant::expiringLeases($days)->with(['user', 'property']);

        $user = $request->user();

        if ($user->isLandlord()) {
            $query->whereHas('property', function ($q) use ($user) {
                $q->where('landlord_id', $user->id);
            });
        }

        $tenants = $query->orderBy('lease_end', 'asc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => TenantResource::collection($tenants),
            'meta' => [
                'current_page' => $tenants->currentPage(),
                'last_page' => $tenants->lastPage(),
                'per_page' => $tenants->perPage(),
                'total' => $tenants->total(),
            ]
        ]);
    }

    public function paymentHistory(Tenant $tenant, Request $request)
    {
        $this->authorize('view', $tenant);

        $payments = $tenant->payments()
            ->with(['paymentMethod', 'processedBy'])
            ->orderBy('due_date', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $payments->items(),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ]
        ]);
    }

    public function statistics(Tenant $tenant)
    {
        $this->authorize('view', $tenant);

        $stats = [
            'lease_status' => $tenant->lease_status,
            'days_until_expiry' => $tenant->days_until_lease_expiry,
            'total_paid' => $tenant->getTotalPaid(),
            'outstanding_balance' => $tenant->getOutstandingBalance(),
            'payment_history' => [
                'total_payments' => $tenant->payments()->count(),
                'completed_payments' => $tenant->payments()->completed()->count(),
                'pending_payments' => $tenant->payments()->pending()->count(),
                'overdue_payments' => $tenant->payments()->overdue()->count(),
            ],
            'maintenance_requests' => [
                'total' => $tenant->maintenanceRequests()->count(),
                'pending' => $tenant->maintenanceRequests()->pending()->count(),
                'in_progress' => $tenant->maintenanceRequests()->inProgress()->count(),
                'completed' => $tenant->maintenanceRequests()->completed()->count(),
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
