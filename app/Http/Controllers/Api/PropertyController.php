<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Property\StorePropertyRequest;
use App\Http\Requests\Property\UpdatePropertyRequest;
use App\Http\Resources\PropertyResource;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $query = Property::query()->with(['landlord', 'city.region', 'tenants']);

        // Filter by landlord if user is a landlord
        if ($request->user()->isLandlord()) {
            $query->where('landlord_id', $request->user()->id);
        }

        // Filter by city
        if ($request->has('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter available properties
        if ($request->has('available') && $request->available) {
            $query->available();
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $properties = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => PropertyResource::collection($properties),
            'meta' => [
                'current_page' => $properties->currentPage(),
                'last_page' => $properties->lastPage(),
                'per_page' => $properties->perPage(),
                'total' => $properties->total(),
            ]
        ]);
    }

    public function store(StorePropertyRequest $request)
    {
        $this->authorize('create', Property::class);

        $property = Property::create(array_merge(
            $request->validated(),
            ['landlord_id' => $request->user()->isLandlord() ? $request->user()->id : $request->landlord_id]
        ));

        return response()->json([
            'success' => true,
            'message' => 'Property created successfully',
            'data' => new PropertyResource($property->load(['landlord', 'city.region']))
        ], 201);
    }

    public function show(Property $property)
    {
        $this->authorize('view', $property);

        return response()->json([
            'success' => true,
            'data' => new PropertyResource($property->load([
                'landlord',
                'city.region',
                'tenants.user',
                'payments' => function($query) {
                    $query->latest()->take(10);
                },
                'maintenanceRequests' => function($query) {
                    $query->latest()->take(10);
                }
            ]))
        ]);
    }

    public function update(UpdatePropertyRequest $request, Property $property)
    {
        $this->authorize('update', $property);

        $property->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Property updated successfully',
            'data' => new PropertyResource($property->load(['landlord', 'city.region']))
        ]);
    }

    public function destroy(Property $property)
    {
        $this->authorize('delete', $property);

        // Check if property has active tenants
        if ($property->tenants()->where('status', 'active')->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete property with active tenants'
            ], 422);
        }

        $property->update(['status' => 'inactive']);

        return response()->json([
            'success' => true,
            'message' => 'Property deactivated successfully'
        ]);
    }

    public function available(Request $request)
    {
        $properties = Property::available()
            ->active()
            ->with(['landlord', 'city.region'])
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => PropertyResource::collection($properties),
            'meta' => [
                'current_page' => $properties->currentPage(),
                'last_page' => $properties->lastPage(),
                'per_page' => $properties->perPage(),
                'total' => $properties->total(),
            ]
        ]);
    }

    public function statistics(Property $property)
    {
        $this->authorize('view', $property);

        $stats = [
            'occupancy_rate' => $property->occupancy_rate,
            'total_units' => $property->total_units,
            'occupied_units' => $property->total_units - $property->available_units,
            'available_units' => $property->available_units,
            'total_revenue' => $property->payments()->completed()->sum('amount'),
            'monthly_revenue' => $property->payments()
                ->completed()
                ->whereMonth('paid_date', now()->month)
                ->whereYear('paid_date', now()->year)
                ->sum('amount'),
            'pending_payments' => $property->payments()->pending()->sum('amount'),
            'overdue_payments' => $property->payments()->overdue()->sum('amount'),
            'maintenance_requests' => [
                'total' => $property->maintenanceRequests()->count(),
                'pending' => $property->maintenanceRequests()->pending()->count(),
                'in_progress' => $property->maintenanceRequests()->inProgress()->count(),
                'completed' => $property->maintenanceRequests()->completed()->count(),
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}





// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use App\Http\Requests\Property\StorePropertyRequest;
// use App\Http\Requests\Property\UpdatePropertyRequest;
// use App\Http\Resources\PropertyResource;
// use App\Models\Property;
// use Illuminate\Http\Request;

// class PropertyController extends Controller
// {
//     public function __construct()
//     {
//         $this->middleware('auth:sanctum');
//     }

//     public function index(Request $request)
//     {
//         $query = Property::query()->with(['landlord', 'city.region', 'tenants']);

//         // Filter by landlord if user is a landlord
//         if ($request->user()->isLandlord()) {
//             $query->where('landlord_id', $request->user()->id);
//         }

//         // Filter by city
//         if ($request->has('city_id')) {
//             $query->where('city_id', $request->city_id);
//         }

//         // Filter by type
//         if ($request->has('type')) {
//             $query->where('type', $request->type);
//         }

//         // Filter by status
//         if ($request->has('status')) {
//             $query->where('status', $request->status);
//         }

//         // Filter available properties
//         if ($request->has('available') && $request->available) {
//             $query->available();
//         }

//         // Search
//         if ($request->has('search')) {
//             $search = $request->search;
//             $query->where(function($q) use ($search) {
//                 $q->where('name', 'like', "%{$search}%")
//                   ->orWhere('address', 'like', "%{$search}%")
//                   ->orWhere('description', 'like', "%{$search}%");
//             });
//         }

//         // Sort
//         $sortBy = $request->get('sort_by', 'created_at');
//         $sortOrder = $request->get('sort_order', 'desc');
//         $query->orderBy($sortBy, $sortOrder);

//         $properties = $query->paginate($request->get('per_page', 15));

//         return response()->json([
//             'success' => true,
//             'data' => PropertyResource::collection($properties),
//             'meta' => [
//                 'current_page' => $properties->currentPage(),
//                 'last_page' => $properties->lastPage(),
//                 'per_page' => $properties->perPage(),
//                 'total' => $properties->total(),
//             ]
//         ]);
//     }

//     public function store(StorePropertyRequest $request)
//     {
//         $this->authorize('create', Property::class);

//         $property = Property::create(array_merge(
//             $request->validated(),
//             ['landlord_id' => $request->user()->isLandlord() ? $request->user()->id : $request->landlord_id]
//         ));

//         return response()->json([
//             'success' => true,
//             'message' => 'Property created successfully',
//             'data' => new PropertyResource($property->load(['landlord', 'city.region']))
//         ], 201);
//     }

//     public function show(Property $property)
//     {
//         $this->authorize('view', $property);

//         return response()->json([
//             'success' => true,
//             'data' => new PropertyResource($property->load([
//                 'landlord',
//                 'city.region',
//                 'tenants.user',
//                 'payments' => function($query) {
//                     $query->latest()->take(10);
//                 },
//                 'maintenanceRequests' => function($query) {
//                     $query->latest()->take(10);
//                 }
//             ]))
//         ]);
//     }

//     public function update(UpdatePropertyRequest $request, Property $property)
//     {
//         $this->authorize('update', $property);

//         $property->update($request->validated());

//         return response()->json([
//             'success' => true,
//             'message' => 'Property updated successfully',
//             'data' => new PropertyResource($property->load(['landlord', 'city.region']))
//         ]);
//     }

//     public function destroy(Property $property)
//     {
//         $this->authorize('delete', $property);

//         // Check if property has active tenants
//         if ($property->tenants()->where('status', 'active')->exists()) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Cannot delete property with active tenants'
//             ], 422);
//         }

//         $property->update(['status' => 'inactive']);

//         return response()->json([
//             'success' => true,
//             'message' => 'Property deactivated successfully'
//         ]);
//     }

//     public function available(Request $request)
//     {
//         $properties = Property::available()
//             ->active()
//             ->with(['landlord', 'city.region'])
//             ->paginate($request->get('per_page', 15));

//         return response()->json([
//             'success' => true,
//             'data' => PropertyResource::collection($properties),
//             'meta' => [
//                 'current_page' => $properties->currentPage(),
//                 'last_page' => $properties->lastPage(),
//                 'per_page' => $properties->perPage(),
//                 'total' => $properties->total(),
//             ]
//         ]);
//     }

//     public function statistics(Property $property)
//     {
//         $this->authorize('view', $property);

//         $stats = [
//             'occupancy_rate' => $property->occupancy_rate,
//             'total_units' => $property->total_units,
//             'occupied_units' => $property->total_units - $property->available_units,
//             'available_units' => $property->available_units,
//             'total_revenue' => $property->payments()->completed()->sum('amount'),
//             'monthly_revenue' => $property->payments()
//                 ->completed()
//                 ->whereMonth('paid_date', now()->month)
//                 ->whereYear('paid_date', now()->year)
//                 ->sum('amount'),
//             'pending_payments' => $property->payments()->pending()->sum('amount'),
//             'overdue_payments' => $property->payments()->overdue()->sum('amount'),
//             'maintenance_requests' => [
//                 'total' => $property->maintenanceRequests()->count(),
//                 'pending' => $property->maintenanceRequests()->pending()->count(),
//                 'in_progress' => $property->maintenanceRequests()->inProgress()->count(),
//                 'completed' => $property->maintenanceRequests()->completed()->count(),
//             ]
//         ];

//         return response()->json([
//             'success' => true,
//             'data' => $stats
//         ]);
//     }
// }