<?php

namespace App\Policies;

use App\Models\MaintenanceRequest;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MaintenanceRequestPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any maintenance requests.
     */
    public function viewAny(User $user)
    {
        return in_array($user->role, ['admin', 'tenant', 'landlord']);
    }

    /**
     * Determine if the user can view a maintenance request.
     */
    public function view(User $user, MaintenanceRequest $maintenanceRequest)
    {
        return $user->role === 'admin' ||
            $maintenanceRequest->tenant_id === $user->id ||
            $maintenanceRequest->property->landlord_id === $user->id;
    }

    /**
     * Determine if the user can create maintenance requests.
     */
    public function create(User $user)
    {
        return $user->role === 'landlord' || $user->role === 'tenant' || $user->role === 'admin';
    }

    /**
     * Determine if the user can update a maintenance request.
     */
    public function update(User $user, MaintenanceRequest $maintenanceRequest)
    {
        return $user->role === 'admin' ||
            $maintenanceRequest->property->landlord_id === $user->id ||
            $maintenanceRequest->assigned_to === $user->id;
    }

    /**
     * Determine if the user can delete a maintenance request.
     */
    public function delete(User $user, MaintenanceRequest $maintenanceRequest)
    {
        return $user->role === 'admin' ||
            $maintenanceRequest->property->landlord_id === $user->id;
    }
}







// <!-- namespace App\Policies;

// use App\Models\MaintenanceRequest;
// use App\Models\User;
// use Illuminate\Auth\Access\HandlesAuthorization;

// class MaintenanceRequestPolicy
// {
//     use HandlesAuthorization;

//     /**
//      * Determine if the user can view any maintenance requests.
//      */
//     public function viewAny(User $user)
//     {
//         return in_array($user->role, ['admin', 'tenant', 'landlord']);
//     }

//     /**
//      * Determine if the user can view a maintenance request.
//      */
//     public function view(User $user, MaintenanceRequest $maintenanceRequest)
//     {
//         return $user->role === 'admin' ||
//             $maintenanceRequest->tenant_id === $user->id ||
//             $maintenanceRequest->property->landlord_id === $user->id;
//     }

//     /**
//      * Determine if the user can create maintenance requests.
//      */
//     public function create(User $user, ?MaintenanceRequest $maintenanceRequest = null)
//     {
//         return in_array($user->role, ['admin', 'tenant', 'landlord']);
//     }

//     /**
//      * Determine if the user can update a maintenance request.
//      */
//     public function update(User $user, MaintenanceRequest $maintenanceRequest)
//     {
//         return $user->role === 'admin' ||
//             $maintenanceRequest->property->landlord_id === $user->id ||
//             $maintenanceRequest->assigned_to === $user->id;
//     }

//     /**
//      * Determine if the user can delete a maintenance request.
//      */
//     public function delete(User $user, MaintenanceRequest $maintenanceRequest)
//     {
//         return $user->role === 'admin' ||
//             $maintenanceRequest->property->landlord_id === $user->id;
//     }
// } -->
