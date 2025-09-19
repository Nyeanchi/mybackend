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
     */
    public function viewAny(User $user)
    {
        return true; // all authenticated users can see lists (controller handles filtering)
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Property $property)
    {
        // Admins can view everything
        if ($user->role === 'admin') {
            return true;
        }

        // Landlords can only view their own properties
        if ($user->role === 'landlord' && $property->landlord_id === $user->id) {
            return true;
        }

        // Tenants can only view properties where they are assigned
        if ($user->role === 'tenant') {
            return $property->tenants()
                ->where('user_id', $user->id)
                ->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user)
    {
        return $user->role === 'landlord' || $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Property $property)
    {
        return ($user->role === 'landlord' && $property->landlord_id === $user->id)
            || $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Property $property)
    {
        return ($user->role === 'landlord' && $property->landlord_id === $user->id)
            || $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Property $property)
    {
        return ($user->role === 'landlord' && $property->landlord_id === $user->id)
            || $user->role === 'admin';
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Property $property)
    {
        return ($user->role === 'landlord' && $property->landlord_id === $user->id)
            || $user->role === 'admin';
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
