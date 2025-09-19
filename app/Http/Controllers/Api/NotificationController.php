<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Mail\WelcomeEmail;
use App\Models\Notification;
use App\Services\SmsService;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

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
     * Send notification (email or SMS) and store in database.
     */
    public function sendNotification(Request $request)
    {
        $request->validate([
            'event_type' => 'required|string|in:registration',
            'user_id' => 'required|exists:users,id',
            'channel' => 'required|string|in:email,sms,both',
        ]);

        $user = User::findOrFail($request->user_id);
        $authUser = auth()->user();

        // Restrict to admin or the user themselves
        if ($authUser->role !== 'admin' && $authUser->id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to send notification for this user',
            ], 403);
        }

        $success = true;
        $messages = [];

        try {
            if ($request->channel === 'email' || $request->channel === 'both') {
                Mail::to($user->email)->send(new WelcomeEmail($user));
                $messages[] = 'Email sent successfully';

                // Store email notification in database
                Notification::create([
                    'recipient_id' => $user->id,
                    'sender_id' => $authUser->id,
                    'type' => 'email_registration',
                    'title' => 'Welcome Email',
                    'message' => 'A welcome email has been sent to your registered email address.',
                    'priority' => 'medium',
                    'data' => json_encode(['event' => 'registration', 'channel' => 'email']),
                ]);
            }

            if ($request->channel === 'sms' || $request->channel === 'both') {
                $smsService = new SmsService();
                $smsMessage = $this->getRoleSpecificSmsMessage($user);
                $smsSent = $smsService->sendSms($user->phone, $smsMessage);

                if ($smsSent) {
                    $messages[] = 'SMS sent successfully';
                } else {
                    $success = false;
                    $messages[] = 'Failed to send SMS';
                }

                // Store SMS notification in database
                Notification::create([
                    'recipient_id' => $user->id,
                    'sender_id' => $authUser->id,
                    'type' => 'sms_registration',
                    'title' => 'Welcome SMS',
                    'message' => $smsMessage,
                    'priority' => 'high',
                    'data' => json_encode(['event' => 'registration', 'channel' => 'sms']),
                ]);
            }

            return response()->json([
                'success' => $success,
                'message' => implode('; ', $messages),
            ], $success ? 200 : 500);
        } catch (\Exception $e) {
            \Log::error('Failed to send notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get role-specific SMS message.
     */
    private function getRoleSpecificSmsMessage($user)
    {
        $baseMessage = "Welcome to Domotena, {$user->first_name}! Your account as a {$user->role} has been created.";

        if ($user->role === 'landlord') {
            return $baseMessage . " List and manage your properties now.";
        } elseif ($user->role === 'tenant') {
            return $baseMessage . " Explore available properties and manage your rentals.";
        }

        return $baseMessage;
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






// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use App\Http\Resources\NotificationResource;
// use App\Mail\WelcomeEmail;
// use App\Models\Notification;
// use App\Services\SmsService;
// use App\Models\User;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Mail;

// class NotificationController extends Controller
// {
//     public function __construct()
//     {
//         $this->middleware('auth:sanctum');
//     }

//     /**
//      * Get a list of notifications for the authenticated user or all notifications (for admins).
//      */
//     public function index(Request $request)
//     {
//         $this->authorize('viewAny', Notification::class);

//         $user = auth()->user();
//         $query = Notification::query()->with(['recipient', 'sender']);

//         // Restrict to user's notifications unless they are an admin
//         if ($user->role !== 'admin') {
//             $query->forUser($user->id);
//         }

//         // Filter by type
//         if ($request->has('type')) {
//             $query->byType($request->type);
//         }

//         // Filter by priority
//         if ($request->has('priority')) {
//             $query->byPriority($request->priority);
//         }

//         // Filter by read/unread status
//         if ($request->has('status')) {
//             $query->when($request->status === 'unread', fn($q) => $q->unread())
//                 ->when($request->status === 'read', fn($q) => $q->read());
//         }

//         // Filter by date range
//         if ($request->has('start_date') && $request->has('end_date')) {
//             $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
//         }

//         // Include only non-expired notifications
//         $query->notExpired();

//         // Sort by created_at (newest first) and paginate
//         $notifications = $query->latest()->paginate($request->get('per_page', 15));

//         return response()->json([
//             'success' => true,
//             'data' => NotificationResource::collection($notifications),
//             'meta' => [
//                 'current_page' => $notifications->currentPage(),
//                 'last_page' => $notifications->lastPage(),
//                 'per_page' => $notifications->perPage(),
//                 'total' => $notifications->total(),
//             ],
//         ]);
//     }

//     /**
//      * Get a list of unread notifications for the authenticated user or all (for admins).
//      */
//     public function unread(Request $request)
//     {
//         $this->authorize('viewAny', Notification::class);

//         $user = auth()->user();
//         $query = Notification::query()->with(['recipient', 'sender'])->unread();

//         // Restrict to user's notifications unless they are an admin
//         if ($user->role !== 'admin') {
//             $query->forUser($user->id);
//         }

//         // Filter by type
//         if ($request->has('type')) {
//             $query->byType($request->type);
//         }

//         // Filter by priority
//         if ($request->has('priority')) {
//             $query->byPriority($request->priority);
//         }

//         // Filter by date range
//         if ($request->has('start_date') && $request->has('end_date')) {
//             $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
//         }

//         // Include only non-expired notifications
//         $query->notExpired();

//         // Sort by created_at (newest first) and paginate
//         $notifications = $query->latest()->paginate($request->get('per_page', 15));

//         return response()->json([
//             'success' => true,
//             'data' => NotificationResource::collection($notifications),
//             'meta' => [
//                 'current_page' => $notifications->currentPage(),
//                 'last_page' => $notifications->lastPage(),
//                 'per_page' => $notifications->perPage(),
//                 'total' => $notifications->total(),
//             ],
//         ]);
//     }

//     /**
//      * Send notification (email or SMS) and store in database.
//      */
//     public function sendNotification(Request $request)
//     {
//         $request->validate([
//             'event_type' => 'required|string|in:registration',
//             'user_id' => 'required|exists:users,id',
//             'channel' => 'required|string|in:email,sms,both',
//         ]);

//         $user = User::findOrFail($request->user_id);
//         $authUser = auth()->user();

//         // Restrict to admin or the user themselves
//         if ($authUser->role !== 'admin' && $authUser->id !== $user->id) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Unauthorized to send notification for this user',
//             ], 403);
//         }

//         $success = true;
//         $messages = [];

//         try {
//             if ($request->channel === 'email' || $request->channel === 'both') {
//                 Mail::to($user->email)->send(new WelcomeEmail($user));
//                 $messages[] = 'Email sent successfully';

//                 // Store email notification in database
//                 Notification::create([
//                     'recipient_id' => $user->id,
//                     'sender_id' => $authUser->id,
//                     'type' => 'email_registration',
//                     'title' => 'Welcome Email',
//                     'message' => 'A welcome email has been sent to your registered email address.',
//                     'priority' => 'medium',
//                     'data' => json_encode(['event' => 'registration', 'channel' => 'email']),
//                 ]);
//             }

//             if ($request->channel === 'sms' || $request->channel === 'both') {
//                 $smsService = new SmsService();
//                 $smsMessage = $this->getRoleSpecificSmsMessage($user);
//                 $smsSent = $smsService->sendSms($user->phone, $smsMessage);

//                 if ($smsSent) {
//                     $messages[] = 'SMS sent successfully';
//                 } else {
//                     $success = false;
//                     $messages[] = 'Failed to send SMS';
//                 }

//                 // Store SMS notification in database
//                 Notification::create([
//                     'recipient_id' => $user->id,
//                     'sender_id' => $authUser->id,
//                     'type' => 'sms_registration',
//                     'title' => 'Welcome SMS',
//                     'message' => $smsMessage,
//                     'priority' => 'high',
//                     'data' => json_encode(['event' => 'registration', 'channel' => 'sms']),
//                 ]);
//             }

//             return response()->json([
//                 'success' => $success,
//                 'message' => implode('; ', $messages),
//             ], $success ? 200 : 500);
//         } catch (\Exception $e) {
//             \Log::error('Failed to send notification: ' . $e->getMessage());
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Failed to send notification: ' . $e->getMessage(),
//             ], 500);
//         }
//     }

//     /**
//      * Get role-specific SMS message.
//      */
//     private function getRoleSpecificSmsMessage($user)
//     {
//         $baseMessage = "Welcome to Domotena, {$user->first_name}! Your account as a {$user->role} has been created.";

//         if ($user->role === 'landlord') {
//             return $baseMessage . " List and manage your properties now.";
//         } elseif ($user->role === 'tenant') {
//             return $baseMessage . " Explore available properties and manage your rentals.";
//         }

//         return $baseMessage;
//     }

//     /**
//      * Mark a notification as read.
//      */
//     public function markAsRead(Request $request, Notification $notification)
//     {
//         $this->authorize('update', $notification);

//         $notification->markAsRead();

//         return response()->json([
//             'success' => true,
//             'message' => 'Notification marked as read',
//             'data' => new NotificationResource($notification->load(['recipient', 'sender'])),
//         ]);
//     }

//     /**
//      * Mark all notifications as read for the authenticated user.
//      */
//     public function markAllAsRead(Request $request)
//     {
//         $this->authorize('markAllAsRead', Notification::class);

//         $user = auth()->user();
//         Notification::forUser($user->id)
//             ->unread()
//             ->notExpired()
//             ->update(['read_at' => now()]);

//         return response()->json([
//             'success' => true,
//             'message' => 'All notifications marked as read',
//         ]);
//     }

//     /**
//      * Delete a notification.
//      */
//     public function destroy(Notification $notification)
//     {
//         $this->authorize('delete', $notification);

//         $notification->delete();

//         return response()->json([
//             'success' => true,
//             'message' => 'Notification deleted successfully',
//         ]);
//     }
// }





// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use App\Http\Resources\NotificationResource;
// use App\Models\Notification;
// use Illuminate\Http\Request;

// class NotificationController extends Controller
// {
//     public function __construct()
//     {
//         $this->middleware('auth:sanctum');
//     }

//     /**
//      * Get a list of notifications for the authenticated user or all notifications (for admins).
//      */
//     public function index(Request $request)
//     {
//         $this->authorize('viewAny', Notification::class);

//         $user = auth()->user();
//         $query = Notification::query()->with(['recipient', 'sender']);

//         // Restrict to user's notifications unless they are an admin
//         if ($user->role !== 'admin') {
//             $query->forUser($user->id);
//         }

//         // Filter by type
//         if ($request->has('type')) {
//             $query->byType($request->type);
//         }

//         // Filter by priority
//         if ($request->has('priority')) {
//             $query->byPriority($request->priority);
//         }

//         // Filter by read/unread status
//         if ($request->has('status')) {
//             $query->when($request->status === 'unread', fn($q) => $q->unread())
//                 ->when($request->status === 'read', fn($q) => $q->read());
//         }

//         // Filter by date range
//         if ($request->has('start_date') && $request->has('end_date')) {
//             $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
//         }

//         // Include only non-expired notifications
//         $query->notExpired();

//         // Sort by created_at (newest first) and paginate
//         $notifications = $query->latest()->paginate($request->get('per_page', 15));

//         return response()->json([
//             'success' => true,
//             'data' => NotificationResource::collection($notifications),
//             'meta' => [
//                 'current_page' => $notifications->currentPage(),
//                 'last_page' => $notifications->lastPage(),
//                 'per_page' => $notifications->perPage(),
//                 'total' => $notifications->total(),
//             ],
//         ]);
//     }

//     /**
//      * Get a list of unread notifications for the authenticated user or all (for admins).
//      */
//     public function unread(Request $request)
//     {
//         $this->authorize('viewAny', Notification::class);

//         $user = auth()->user();
//         $query = Notification::query()->with(['recipient', 'sender'])->unread();

//         // Restrict to user's notifications unless they are an admin
//         if ($user->role !== 'admin') {
//             $query->forUser($user->id);
//         }

//         // Filter by type
//         if ($request->has('type')) {
//             $query->byType($request->type);
//         }

//         // Filter by priority
//         if ($request->has('priority')) {
//             $query->byPriority($request->priority);
//         }

//         // Filter by date range
//         if ($request->has('start_date') && $request->has('end_date')) {
//             $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
//         }

//         // Include only non-expired notifications
//         $query->notExpired();

//         // Sort by created_at (newest first) and paginate
//         $notifications = $query->latest()->paginate($request->get('per_page', 15));

//         return response()->json([
//             'success' => true,
//             'data' => NotificationResource::collection($notifications),
//             'meta' => [
//                 'current_page' => $notifications->currentPage(),
//                 'last_page' => $notifications->lastPage(),
//                 'per_page' => $notifications->perPage(),
//                 'total' => $notifications->total(),
//             ],
//         ]);
//     }

//     /**
//      * Mark a notification as read.
//      */
//     public function markAsRead(Request $request, Notification $notification)
//     {
//         $this->authorize('update', $notification);

//         $notification->markAsRead();

//         return response()->json([
//             'success' => true,
//             'message' => 'Notification marked as read',
//             'data' => new NotificationResource($notification->load(['recipient', 'sender'])),
//         ]);
//     }

//     /**
//      * Mark all notifications as read for the authenticated user.
//      */
//     public function markAllAsRead(Request $request)
//     {
//         $this->authorize('markAllAsRead', Notification::class);

//         $user = auth()->user();
//         Notification::forUser($user->id)
//             ->unread()
//             ->notExpired()
//             ->update(['read_at' => now()]);

//         return response()->json([
//             'success' => true,
//             'message' => 'All notifications marked as read',
//         ]);
//     }

//     /**
//      * Delete a notification.
//      */
//     public function destroy(Notification $notification)
//     {
//         $this->authorize('delete', $notification);

//         $notification->delete();

//         return response()->json([
//             'success' => true,
//             'message' => 'Notification deleted successfully',
//         ]);
//     }
// }