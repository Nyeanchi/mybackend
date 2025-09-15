<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\MaintenanceRequest\StoreMaintenanceRequestRequest;
use App\Http\Requests\MaintenanceRequest\UpdateMaintenanceRequestRequest;
use App\Http\Resources\MaintenanceRequestResource;
use App\Models\MaintenanceRequest;
use App\Models\Notification;
use Illuminate\Http\Request;

class MaintenanceRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        
        $query = MaintenanceRequest::query()->with(['tenant', 'property', 'assignedTo', 'property.landlord']);

        $user = $request->user();

        // Filter by user role
        if ($user->isTenant()) {
            $query->where('tenant_id', $user->id);
        } elseif ($user->isLandlord()) {
            $query->whereHas('property', function ($q) use ($user) {
                $q->where('landlord_id', $user->id);
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by property
        if ($request->has('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        // Filter urgent requests
        if ($request->has('urgent') && $request->urgent) {
            $query->urgent();
        }

        // Filter overdue requests
        if ($request->has('overdue') && $request->overdue) {
            $query->overdue();
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('tenant', function ($subQ) use ($search) {
                        $subQ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $requests = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => MaintenanceRequestResource::collection($requests),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ]
        ]);
    }

    public function store(StoreMaintenanceRequestRequest $request)
    {
        $this->authorize('create', MaintenanceRequest::class);

        $maintenanceRequest = MaintenanceRequest::create(array_merge(
            $request->validated(),
            ['tenant_id' => $request->user()->isTenant() ? $request->user()->id : $request->tenant_id]
        ));

        // Notify landlord about new maintenance request
        if ($maintenanceRequest->property->landlord) {
            Notification::create([
                'recipient_id' => $maintenanceRequest->property->landlord_id,
                'type' => 'maintenance_request',
                'title' => 'New Maintenance Request',
                'message' => 'A new maintenance request has been submitted for ' . $maintenanceRequest->property->name,
                'data' => ['request_id' => $maintenanceRequest->id],
                'priority' => $maintenanceRequest->priority === 'urgent' ? 'high' : 'medium',
                'action_url' => '/maintenance/' . $maintenanceRequest->id,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Maintenance request created successfully',
            'data' => new MaintenanceRequestResource($maintenanceRequest->load(['tenant', 'property', 'assignedTo', 'property.landlord']))
        ], 201);
    }

    public function show(MaintenanceRequest $maintenanceRequest)
    {
        $this->authorize('view', $maintenanceRequest);

        return response()->json([
            'success' => true,
            'data' => new MaintenanceRequestResource($maintenanceRequest->load(['tenant', 'property', 'assignedTo', 'property.landlord']))
        ]);
    }

    public function update(UpdateMaintenanceRequestRequest $request, MaintenanceRequest $maintenanceRequest)
    {
        $this->authorize('update', $maintenanceRequest);

        $maintenanceRequest->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Maintenance request updated successfully',
            'data' => new MaintenanceRequestResource($maintenanceRequest->load(['tenant', 'property', 'assignedTo', 'property.landlord']))
        ]);
    }

    public function destroy(MaintenanceRequest $maintenanceRequest)
    {
        $this->authorize('delete', $maintenanceRequest);

        $maintenanceRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Maintenance request deleted successfully'
        ]);
    }

    public function assign(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $this->authorize('update', $maintenanceRequest);

        $request->validate([
            'assigned_to' => 'nullable|exists:users,id',
            'scheduled_date' => 'nullable|date|after:now',
            'estimated_cost' => 'nullable|numeric|min:0',
        ]);

        $maintenanceRequest->markAsInProgress(
            $request->assigned_to,
            $request->scheduled_date,
            $request->estimated_cost
        );

        // Notify tenant about assignment
        Notification::createMaintenanceUpdate(
            $maintenanceRequest->tenant_id,
            $maintenanceRequest->id,
            'in_progress'
        );

        return response()->json([
            'success' => true,
            'message' => 'Maintenance request assigned successfully',
            'data' => new MaintenanceRequestResource($maintenanceRequest->load(['tenant', 'property', 'assignedTo', 'property.landlord']))
        ]);
    }

    public function complete(Request $request, MaintenanceRequest $maintenanceRequest)
    {
        $this->authorize('update', $maintenanceRequest);

        $request->validate([
            'actual_cost' => 'nullable|numeric|min:0',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $maintenanceRequest->markAsCompleted(
            $request->actual_cost,
            $request->admin_notes
        );

        // Notify tenant about completion
        Notification::createMaintenanceUpdate(
            $maintenanceRequest->tenant_id,
            $maintenanceRequest->id,
            'completed'
        );

        return response()->json([
            'success' => true,
            'message' => 'Maintenance request marked as completed',
            'data' => new MaintenanceRequestResource($maintenanceRequest->load(['tenant', 'property', 'assignedTo', 'property.landlord']))
        ]);
    }

    public function pending(Request $request)
    {
        $query = MaintenanceRequest::pending()->with(['tenant', 'property', 'property.landlord']);

        $user = $request->user();

        if ($user->isTenant()) {
            $query->where('tenant_id', $user->id);
        } elseif ($user->isLandlord()) {
            $query->whereHas('property', function ($q) use ($user) {
                $q->where('landlord_id', $user->id);
            });
        }

        $requests = $query->orderBy('priority', 'desc')
            ->orderBy('created_at', 'asc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => MaintenanceRequestResource::collection($requests),
            'meta' => [
                'current_page' => $requests->currentPage(),
                'last_page' => $requests->lastPage(),
                'per_page' => $requests->perPage(),
                'total' => $requests->total(),
            ]
        ]);
    }

    public function statistics(Request $request)
    {
        $user = $request->user();
        $query = MaintenanceRequest::query();

        // Apply user-specific filters
        if ($user->isTenant()) {
            $query->where('tenant_id', $user->id);
        } elseif ($user->isLandlord()) {
            $query->whereHas('property', function ($q) use ($user) {
                $q->where('landlord_id', $user->id);
            });
        }

        $stats = [
            'total_requests' => $query->clone()->count(),
            'pending_requests' => $query->clone()->pending()->count(),
            'in_progress_requests' => $query->clone()->inProgress()->count(),
            'completed_requests' => $query->clone()->completed()->count(),
            'urgent_requests' => $query->clone()->urgent()->count(),
            'overdue_requests' => $query->clone()->overdue()->count(),
            'average_resolution_time' => $query->clone()->completed()
                ->selectRaw('AVG(DATEDIFF(completed_at, created_at)) as avg_days')
                ->value('avg_days'),
            'total_cost' => $query->clone()->completed()->sum('actual_cost'),
            'monthly_requests' => $query->clone()
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}








// <!-- 

// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use App\Http\Requests\MaintenanceRequest\StoreMaintenanceRequestRequest;
// use App\Http\Requests\MaintenanceRequest\UpdateMaintenanceRequestRequest;
// use App\Http\Resources\MaintenanceRequestResource;
// use App\Models\MaintenanceRequest;
// use App\Models\Notification;
// use Illuminate\Http\Request;

// class MaintenanceRequestController extends Controller
// {
//     public function __construct()
//     {
//         $this->middleware('auth:sanctum');
//     }

//     public function index(Request $request)
//     {
        
//         $query = MaintenanceRequest::query()->with(['tenant', 'property', 'assignedTo']);

//         $user = $request->user();

//         // Filter by user role
//         if ($user->isTenant()) {
//             $query->where('tenant_id', $user->id);
//         } elseif ($user->isLandlord()) {
//             $query->whereHas('property', function ($q) use ($user) {
//                 $q->where('landlord_id', $user->id);
//             });
//         }

//         // Filter by status
//         if ($request->has('status')) {
//             $query->where('status', $request->status);
//         }

//         // Filter by priority
//         if ($request->has('priority')) {
//             $query->where('priority', $request->priority);
//         }

//         // Filter by category
//         if ($request->has('category')) {
//             $query->where('category', $request->category);
//         }

//         // Filter by property
//         if ($request->has('property_id')) {
//             $query->where('property_id', $request->property_id);
//         }

//         // Filter urgent requests
//         if ($request->has('urgent') && $request->urgent) {
//             $query->urgent();
//         }

//         // Filter overdue requests
//         if ($request->has('overdue') && $request->overdue) {
//             $query->overdue();
//         }

//         // Search
//         if ($request->has('search')) {
//             $search = $request->search;
//             $query->where(function ($q) use ($search) {
//                 $q->where('title', 'like', "%{$search}%")
//                     ->orWhere('description', 'like', "%{$search}%")
//                     ->orWhereHas('tenant', function ($subQ) use ($search) {
//                         $subQ->where('first_name', 'like', "%{$search}%")
//                             ->orWhere('last_name', 'like', "%{$search}%");
//                     });
//             });
//         }

//         // Sort
//         $sortBy = $request->get('sort_by', 'created_at');
//         $sortOrder = $request->get('sort_order', 'desc');
//         $query->orderBy($sortBy, $sortOrder);

//         $requests = $query->paginate($request->get('per_page', 15));

//         return response()->json([
//             'success' => true,
//             'data' => MaintenanceRequestResource::collection($requests),
//             'meta' => [
//                 'current_page' => $requests->currentPage(),
//                 'last_page' => $requests->lastPage(),
//                 'per_page' => $requests->perPage(),
//                 'total' => $requests->total(),
//             ]
//         ]);
//     }

//     public function store(StoreMaintenanceRequestRequest $request)
//     {
//         $this->authorize('create', MaintenanceRequest::class);

//         $maintenanceRequest = MaintenanceRequest::create(array_merge(
//             $request->validated(),
//             ['tenant_id' => $request->user()->isTenant() ? $request->user()->id : $request->tenant_id]
//         ));

//         // Notify landlord about new maintenance request
//         if ($maintenanceRequest->property->landlord) {
//             Notification::create([
//                 'recipient_id' => $maintenanceRequest->property->landlord_id,
//                 'type' => 'maintenance_request',
//                 'title' => 'New Maintenance Request',
//                 'message' => 'A new maintenance request has been submitted for ' . $maintenanceRequest->property->name,
//                 'data' => ['request_id' => $maintenanceRequest->id],
//                 'priority' => $maintenanceRequest->priority === 'urgent' ? 'high' : 'medium',
//                 'action_url' => '/maintenance/' . $maintenanceRequest->id,
//             ]);
//         }

//         return response()->json([
//             'success' => true,
//             'message' => 'Maintenance request created successfully',
//             'data' => new MaintenanceRequestResource($maintenanceRequest->load(['tenant', 'property']))
//         ], 201);
//     }

//     public function show(MaintenanceRequest $maintenanceRequest)
//     {
//         $this->authorize('view', $maintenanceRequest);

//         return response()->json([
//             'success' => true,
//             'data' => new MaintenanceRequestResource($maintenanceRequest->load(['tenant', 'property', 'assignedTo']))
//         ]);
//     }

//     public function update(UpdateMaintenanceRequestRequest $request, MaintenanceRequest $maintenanceRequest)
//     {
//         $this->authorize('update', $maintenanceRequest);

//         $oldStatus = $maintenanceRequest->status;
//         $maintenanceRequest->update($request->validated());

//         // Send notification if status changed
//         if ($oldStatus !== $maintenanceRequest->status) {
//             Notification::createMaintenanceUpdate(
//                 $maintenanceRequest->tenant_id,
//                 $maintenanceRequest->id,
//                 $maintenanceRequest->status
//             );
//         }

//         return response()->json([
//             'success' => true,
//             'message' => 'Maintenance request updated successfully',
//             'data' => new MaintenanceRequestResource($maintenanceRequest->load(['tenant', 'property', 'assignedTo']))
//         ]);
//     }

//     public function destroy(MaintenanceRequest $maintenanceRequest)
//     {
//         $this->authorize('delete', $maintenanceRequest);

//         if ($maintenanceRequest->status === 'completed') {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Cannot delete completed maintenance request'
//             ], 422);
//         }

//         $maintenanceRequest->delete();

//         return response()->json([
//             'success' => true,
//             'message' => 'Maintenance request deleted successfully'
//         ]);
//     }

//     public function assign(Request $request, MaintenanceRequest $maintenanceRequest)
//     {
//         $this->authorize('update', $maintenanceRequest);

//         $request->validate([
//             'assigned_to' => 'nullable|exists:users,id',
//             'scheduled_date' => 'nullable|date|after:now',
//             'estimated_cost' => 'nullable|numeric|min:0',
//         ]);

//         $maintenanceRequest->markAsInProgress(
//             $request->assigned_to,
//             $request->scheduled_date,
//             $request->estimated_cost
//         );

//         // Notify tenant about assignment
//         Notification::createMaintenanceUpdate(
//             $maintenanceRequest->tenant_id,
//             $maintenanceRequest->id,
//             'in_progress'
//         );

//         return response()->json([
//             'success' => true,
//             'message' => 'Maintenance request assigned successfully',
//             'data' => new MaintenanceRequestResource($maintenanceRequest->load(['tenant', 'property', 'assignedTo']))
//         ]);
//     }

//     public function complete(Request $request, MaintenanceRequest $maintenanceRequest)
//     {
//         $this->authorize('update', $maintenanceRequest);

//         $request->validate([
//             'actual_cost' => 'nullable|numeric|min:0',
//             'admin_notes' => 'nullable|string|max:1000',
//         ]);

//         $maintenanceRequest->markAsCompleted(
//             $request->actual_cost,
//             $request->admin_notes
//         );

//         // Notify tenant about completion
//         Notification::createMaintenanceUpdate(
//             $maintenanceRequest->tenant_id,
//             $maintenanceRequest->id,
//             'completed'
//         );

//         return response()->json([
//             'success' => true,
//             'message' => 'Maintenance request marked as completed',
//             'data' => new MaintenanceRequestResource($maintenanceRequest->load(['tenant', 'property', 'assignedTo']))
//         ]);
//     }

//     public function pending(Request $request)
//     {
//         $query = MaintenanceRequest::pending()->with(['tenant', 'property']);

//         $user = $request->user();

//         if ($user->isTenant()) {
//             $query->where('tenant_id', $user->id);
//         } elseif ($user->isLandlord()) {
//             $query->whereHas('property', function ($q) use ($user) {
//                 $q->where('landlord_id', $user->id);
//             });
//         }

//         $requests = $query->orderBy('priority', 'desc')
//             ->orderBy('created_at', 'asc')
//             ->paginate($request->get('per_page', 15));

//         return response()->json([
//             'success' => true,
//             'data' => MaintenanceRequestResource::collection($requests),
//             'meta' => [
//                 'current_page' => $requests->currentPage(),
//                 'last_page' => $requests->lastPage(),
//                 'per_page' => $requests->perPage(),
//                 'total' => $requests->total(),
//             ]
//         ]);
//     }

//     public function statistics(Request $request)
//     {
//         $user = $request->user();
//         $query = MaintenanceRequest::query();

//         // Apply user-specific filters
//         if ($user->isTenant()) {
//             $query->where('tenant_id', $user->id);
//         } elseif ($user->isLandlord()) {
//             $query->whereHas('property', function ($q) use ($user) {
//                 $q->where('landlord_id', $user->id);
//             });
//         }

//         $stats = [
//             'total_requests' => $query->clone()->count(),
//             'pending_requests' => $query->clone()->pending()->count(),
//             'in_progress_requests' => $query->clone()->inProgress()->count(),
//             'completed_requests' => $query->clone()->completed()->count(),
//             'urgent_requests' => $query->clone()->urgent()->count(),
//             'overdue_requests' => $query->clone()->overdue()->count(),
//             'average_resolution_time' => $query->clone()->completed()
//                 ->selectRaw('AVG(DATEDIFF(completed_at, created_at)) as avg_days')
//                 ->value('avg_days'),
//             'total_cost' => $query->clone()->completed()->sum('actual_cost'),
//             'monthly_requests' => $query->clone()
//                 ->whereMonth('created_at', now()->month)
//                 ->whereYear('created_at', now()->year)
//                 ->count(),
//         ];

//         return response()->json([
//             'success' => true,
//             'data' => $stats
//         ]);
//     }
// } -->
