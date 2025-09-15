<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    /**
     * Get a list of notifications for the authenticated user or all notifications (for admins).
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Notification::class);

        $user = auth()->user();
        $query = Notification::query()->with(['recipient', 'sender']);

        // Restrict to user's notifications unless they are an admin
        if ($user->role !== 'admin') {
            $query->forUser($user->id);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->byType($request->type);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->byPriority($request->priority);
        }

        // Filter by read/unread status
        if ($request->has('status')) {
            $query->when($request->status === 'unread', fn($q) => $q->unread())
                  ->when($request->status === 'read', fn($q) => $q->read());
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        // Include only non-expired notifications
        $query->notExpired();

        // Sort by created_at (newest first) and paginate
        $notifications = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => NotificationResource::collection($notifications),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    /**
     * Get a list of unread notifications for the authenticated user or all (for admins).
     */
    public function unread(Request $request)
    {
        $this->authorize('viewAny', Notification::class);

        $user = auth()->user();
        $query = Notification::query()->with(['recipient', 'sender'])->unread();

        // Restrict to user's notifications unless they are an admin
        if ($user->role !== 'admin') {
            $query->forUser($user->id);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->byType($request->type);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->byPriority($request->priority);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        // Include only non-expired notifications
        $query->notExpired();

        // Sort by created_at (newest first) and paginate
        $notifications = $query->latest()->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => NotificationResource::collection($notifications),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
            ],
        ]);
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Request $request, Notification $notification)
    {
        $this->authorize('update', $notification);

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => new NotificationResource($notification->load(['recipient', 'sender'])),
        ]);
    }

    /**
     * Mark all notifications as read for the authenticated user.
     */
    public function markAllAsRead(Request $request)
    {
        $this->authorize('markAllAsRead', Notification::class);

        $user = auth()->user();
        Notification::forUser($user->id)
            ->unread()
            ->notExpired()
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(Notification $notification)
    {
        $this->authorize('delete', $notification);

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully',
        ]);
    }
}
