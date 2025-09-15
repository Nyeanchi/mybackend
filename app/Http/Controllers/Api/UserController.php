<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        
        $query = User::query()->with(['city.region', 'settings']);

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($users),
            'meta' => [
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
            ]
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => $request->status ?? 'active',
            'city_id' => $request->city_id,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
        ]);

        // Create default user settings
        UserSetting::create(array_merge(
            ['user_id' => $user->id],
            UserSetting::getDefaultSettings()
        ));

        return response()->json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => new UserResource($user->load(['city.region', 'settings']))
        ], 201);
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        return response()->json([
            'success' => true,
            'data' => new UserResource($user->load(['city.region', 'settings', 'properties', 'tenantProfile']))
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        $data = $request->validated();

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return response()->json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => new UserResource($user->load(['city.region', 'settings']))
        ]);
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        // Soft delete or deactivate instead of hard delete
        $user->update(['status' => 'inactive']);

        return response()->json([
            'success' => true,
            'message' => 'User deactivated successfully'
        ]);
    }

    public function activate(User $user)
    {
        $this->authorize('update', $user);

        $user->update(['status' => 'active']);

        return response()->json([
            'success' => true,
            'message' => 'User activated successfully',
            'data' => new UserResource($user)
        ]);
    }

    public function landlords(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $landlords = User::landlords()
            ->active()
            ->with(['city.region', 'properties'])
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($landlords),
            'meta' => [
                'current_page' => $landlords->currentPage(),
                'last_page' => $landlords->lastPage(),
                'per_page' => $landlords->perPage(),
                'total' => $landlords->total(),
            ]
        ]);
    }

    public function tenants(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $tenants = User::tenants()
            ->active()
            ->with(['city.region', 'tenantProfile.property'])
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => UserResource::collection($tenants),
            'meta' => [
                'current_page' => $tenants->currentPage(),
                'last_page' => $tenants->lastPage(),
                'per_page' => $tenants->perPage(),
                'total' => $tenants->total(),
            ]
        ]);
    }
}
