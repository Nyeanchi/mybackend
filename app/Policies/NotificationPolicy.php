<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotificationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any notifications.
     */
    public function viewAny(User $user)
    {
        // All roles (admin, tenant, landlord) can view their own notifications
        return in_array($user->role, ['admin', 'tenant', 'landlord']);
    }

    /**
     * Determine if the user can view a specific notification.
     */
    public function view(User $user, Notification $notification)
    {
        // Admins can view any notification, others can only view their own
        return $user->role === 'admin' || $notification->recipient_id === $user->id;
    }

    /**
     * Determine if the user can update a notification (e.g., mark as read).
     */
    public function update(User $user, Notification $notification)
    {
        // Admins can update any notification, others can only update their own
        return $user->role === 'admin' || $notification->recipient_id === $user->id;
    }

    /**
     * Determine if the user can delete a notification.
     */
    public function delete(User $user, Notification $notification)
    {
        // Admins can delete any notification, others can only delete their own
        return $user->role === 'admin' || $notification->recipient_id === $user->id;
    }

    /**
     * Determine if the user can mark all their notifications as read.
     */
    public function markAllAsRead(User $user)
    {
        // All roles can mark their own notifications as read
        return in_array($user->role, ['admin', 'tenant', 'landlord']);
    }
}
