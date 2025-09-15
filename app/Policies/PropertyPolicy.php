<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PropertyPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny(User $user)
    {
        return true; // Allow all authenticated users to list properties (filtered by controller)
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Property  $property
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view(User $user, Property $property)
    {
        if ($user->role === 'admin') {
            return true; // Admins can view all properties
        }
        if ($user->role === 'landlord' && $property->landlord_id === $user->id) {
            return true; // Landlords can view their own properties
        }
        if ($user->role === 'tenant' && $property->tenants()->where('id', $user->id)->exists()) {
            return true; // Tenants can view their assigned properties
        }
        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create(User $user, Property $property)
    {
        //return $user->role === 'landlord' || $user->role === 'admin';
        if ($user->role === 'admin') {
            return true; // Admins can view all properties
        }
        if ($user->role === 'landlord' && $property->landlord_id === $user->id) {
            return true; // Landlords can view their own properties
        }
        if ($user->role === 'tenant' && $property->tenants()->where('id', $user->id)->exists()) {
            return true; // Tenants can view their assigned properties
        }
        return false;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Property  $property
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update(User $user, Property $property)
    {
        return $user->role === 'landlord' && $property->landlord_id === $user->id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Property  $property
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete(User $user, Property $property)
    {
        return $user->role === 'landlord' && $property->landlord_id === $user->id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Property  $property
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore(User $user, Property $property)
    {
        return $user->role === 'landlord' && $property->landlord_id === $user->id || $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Property  $property
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete(User $user, Property $property)
    {
        return $user->role === 'landlord' && $property->landlord_id === $user->id || $user->role === 'admin';
    }
}




// namespace App\Policies;

// use App\Models\Property;
// use App\Models\User;
// use Illuminate\Auth\Access\HandlesAuthorization;

// class PropertyPolicy
// {
//     use HandlesAuthorization;

//     /**
//      * Determine whether the user can view any models.
//      *
//      * @param  \App\Models\User  $user
//      * @return \Illuminate\Auth\Access\Response|bool
//      */
//     public function viewAny(User $user)
//     {
//         //
//     }

//     /**
//      * Determine whether the user can view the model.
//      *
//      * @param  \App\Models\User  $user
//      * @param  \App\Models\Property  $property
//      * @return \Illuminate\Auth\Access\Response|bool
//      */
//     public function view(User $user, Property $property)
//     {
//         //
//         return $user->role === 'landlord' && $property->landlord_id === $user->id;
//     }

//     /**
//      * Determine whether the user can create models.
//      *
//      * @param  \App\Models\User  $user
//      * @return \Illuminate\Auth\Access\Response|bool
//      * 
//      */

    
//     public function create(User $user)
//     {
//         return $user->role === 'landlord';
//     }

//     /**
//      * Determine whether the user can update the model.
//      *
//      * @param  \App\Models\User  $user
//      * @param  \App\Models\Property  $property
//      * @return \Illuminate\Auth\Access\Response|bool
//      */
//     public function update(User $user, Property $property)
//     {
//         //
//         return $user->role === 'landlord';
//     }

//     /**
//      * Determine whether the user can delete the model.
//      *
//      * @param  \App\Models\User  $user
//      * @param  \App\Models\Property  $property
//      * @return \Illuminate\Auth\Access\Response|bool
//      */
//     public function delete(User $user, Property $property)
//     {
//         //
//     }

//     /**
//      * Determine whether the user can restore the model.
//      *
//      * @param  \App\Models\User  $user
//      * @param  \App\Models\Property  $property
//      * @return \Illuminate\Auth\Access\Response|bool
//      */
//     public function restore(User $user, Property $property)
//     {
//         //
//     }

//     /**
//      * Determine whether the user can permanently delete the model.
//      *
//      * @param  \App\Models\User  $user
//      * @param  \App\Models\Property  $property
//      * @return \Illuminate\Auth\Access\Response|bool
//      */
//     public function forceDelete(User $user, Property $property)
//     {
//         //
//     }
// }
