<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeEmail;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\JsonResponse;

class EmailController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function sendEmail(Request $request): JsonResponse
    {
        $request->validate([
            'event_type' => 'required|string|in:registration,tenant_registration',
            'user_id' => 'required|exists:users,id',
            'password' => 'nullable|string|min:8', // Optional for tenant registration
        ]);

        $user = User::findOrFail($request->user_id);

        // Restrict to admin or the user themselves
        if (auth()->user()->role !== 'admin' && auth()->user()->id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to send email for this user',
            ], 403);
        }

        try {
            $password = $request->event_type === 'tenant_registration' ? ($request->password ?? Str::random(12)) : null;
            Mail::to($user->email)->send(new WelcomeEmail($user, $password));

            // Store notification in database
            Notification::create([
                'recipient_id' => $user->id,
                'sender_id' => auth()->id(),
                'type' => $request->event_type === 'registration' ? 'email_registration' : 'email_tenant_registration',
                'title' => $request->event_type === 'registration' ? 'Welcome Email Sent' : 'Tenant Welcome Email Sent',
                'message' => $request->event_type === 'registration' ? 'A welcome email has been sent to your registered email address.' : 'A welcome email has been sent to the new tenant.',
                'priority' => 'medium',
                'data' => json_encode([
                    'event' => $request->event_type,
                    'channel' => 'email',
                    'password' => $password,
                ]),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email sent successfully',
                'password' => $password,
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Failed to send email: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage(),
            ], 500);
        }
    }
}




// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use App\Mail\WelcomeEmail;
// use App\Models\User;
// use Illuminate\Http\Request;
// use Illuminate\Support\Facades\Mail;
// use Illuminate\Http\JsonResponse;

// class EmailController extends Controller
// {
//     public function __construct()
//     {
//         $this->middleware('auth:sanctum');
//     }

//     public function sendEmail(Request $request): JsonResponse
//     {
//         $request->validate([
//             'event_type' => 'required|string|in:registration',
//             'user_id' => 'required|exists:users,id',
//         ]);

//         $user = User::findOrFail($request->user_id);

//         // Restrict to admin or the user themselves
//         if (auth()->user()->role !== 'admin' && auth()->user()->id !== $user->id) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Unauthorized to send email for this user',
//             ], 403);
//         }

//         try {
//             if ($request->event_type === 'registration') {
//                 Mail::to($user->email)->send(new WelcomeEmail($user));
//             }

//             return response()->json([
//                 'success' => true,
//                 'message' => 'Email sent successfully',
//             ], 200);
//         } catch (\Exception $e) {
//             \Log::error('Failed to send email: ' . $e->getMessage());
//             return response()->json([
//                 'success' => false,
//                 'message' => 'Failed to send email: ' . $e->getMessage(),
//             ], 500);
//         }
//     }
// }
