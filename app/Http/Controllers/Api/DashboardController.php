<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Property;
use App\Models\Tenant;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\MaintenanceRequest;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics.
     */
    public function stats()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            // Admin stats
            $totalUsers = User::count();
            $totalProperties = Property::count();
            $monthlyRevenue = Payment::where('status', 'completed')->sum('amount');
            $pendingVerifications = User::where('status', 'pending')->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_users' => $totalUsers,
                    'total_properties' => $totalProperties,
                    'monthly_revenue' => $monthlyRevenue,
                    'pending_verifications' => $pendingVerifications,
                ],
            ]);
        } elseif ($user->role === 'landlord') {
            // Landlord stats
            $totalProperties = Property::where('landlord_id', $user->id)->count();
            $totalTenants = Tenant::whereHas('property', function ($query) use ($user) {
                $query->where('landlord_id', $user->id);
            })->count();
            $monthlyRevenue = Payment::where('status', 'completed')
                ->whereHas('property', function ($query) use ($user) {
                    $query->where('landlord_id', $user->id);
                })
                ->sum('amount');
            $occupancyRate = Property::where('landlord_id', $user->id)
                ->where('available_units', true)
                ->count() / max(1, $totalProperties) * 100;

            return response()->json([
                'success' => true,
                'data' => [
                    'total_properties' => $totalProperties,
                    'total_tenants' => $totalTenants,
                    'monthly_revenue' => $monthlyRevenue,
                    'occupancy_rate' => $occupancyRate,
                ],
            ]);
        } elseif ($user->role === 'tenant') {
            // Tenant stats
            $currentRent = Payment::where('tenant_id', $user->id)
                ->where('status', 'pending')
                ->orderBy('due_date', 'asc')
                ->first();
            $nextPayment = Payment::where('tenant_id', $user->id)
                ->where('status', 'pending')
                ->orderBy('due_date', 'asc')
                ->skip(1)
                ->first();
            $paymentStatus = $currentRent ? $currentRent->status : 'No pending payments';
            $totalMaintenanceRequests = MaintenanceRequest::where('tenant_id', $user->id)->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'current_rent' => $currentRent ? $currentRent->amount : null,
                    'next_payment' => $nextPayment ? $nextPayment->amount : null,
                    'payment_status' => $paymentStatus,
                    'total_maintenance_requests' => $totalMaintenanceRequests,
                ],
            ]);
        }
    }

    /**
     * Get recent activities.
     */
    public function recentActivities()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            // Admin recent activities
            $recentUsers = User::latest()->take(5)->get(['id', 'first_name', 'last_name', 'created_at']);
            $recentPayments = Payment::latest()->take(5)->get(['id', 'amount', 'status', 'created_at']);
            $recentProperties = Property::latest()->take(5)->get(['id', 'name', 'created_at']);

            return response()->json([
                'success' => true,
                'data' => [
                    'recent_users' => $recentUsers,
                    'recent_payments' => $recentPayments,
                    'recent_properties' => $recentProperties,
                ],
            ]);
        } elseif ($user->role === 'landlord') {
            // Landlord recent activities
            $recentPayments = Payment::whereHas('property', function ($query) use ($user) {
                $query->where('landlord_id', $user->id);
            })->latest()->take(5)->get(['id', 'amount', 'status', 'created_at']);
            $recentMaintenanceRequests = MaintenanceRequest::whereHas('property', function ($query) use ($user) {
                $query->where('landlord_id', $user->id);
            })->latest()->take(5)->get(['id', 'title', 'status', 'created_at']);

            return response()->json([
                'success' => true,
                'data' => [
                    'recent_payments' => $recentPayments,
                    'recent_maintenance_requests' => $recentMaintenanceRequests,
                ],
            ]);
        } elseif ($user->role === 'tenant') {
            // Tenant recent activities
            $recentPayments = Payment::where('tenant_id', $user->id)
                ->latest()
                ->take(5)
                ->get(['id', 'amount', 'status', 'created_at']);
            $recentMaintenanceRequests = MaintenanceRequest::where('tenant_id', $user->id)
                ->latest()
                ->take(5)
                ->get(['id', 'title', 'status', 'created_at']);

            return response()->json([
                'success' => true,
                'data' => [
                    'recent_payments' => $recentPayments,
                    'recent_maintenance_requests' => $recentMaintenanceRequests,
                ],
            ]);
        }
    }

    /**
     * Get analytics data.
     */
    public function analytics()
    {
        $user = auth()->user();

        if ($user->role === 'admin') {
            // Admin analytics
            $monthlySignups = User::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->groupBy('month')
                ->orderBy('month')
                ->get();
            $monthlyRevenue = Payment::selectRaw('MONTH(paid_date) as month, SUM(amount) as total')
                ->where('status', 'completed')
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'monthly_signups' => $monthlySignups,
                    'monthly_revenue' => $monthlyRevenue,
                ],
            ]);
        } elseif ($user->role === 'landlord') {
            // Landlord analytics
            $monthlyRevenue = Payment::selectRaw('MONTH(paid_date) as month, SUM(amount) as total')
                ->where('status', 'completed')
                ->whereHas('property', function ($query) use ($user) {
                    $query->where('landlord_id', $user->id);
                })
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'monthly_revenue' => $monthlyRevenue,
                ],
            ]);
        }
    }


    public function revenueReport(Request $request)
    {
        $query = \App\Models\Payment::query()->completed();

        // Apply filters for date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('paid_date', [$request->start_date, $request->end_date]);
        }

        // Calculate total revenue
        $totalRevenue = $query->sum('amount');

        // Group revenue by month
        $monthlyRevenue = $query->selectRaw('YEAR(paid_date) as year, MONTH(paid_date) as month, SUM(amount) as total')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_revenue' => $totalRevenue,
                'monthly_revenue' => $monthlyRevenue,
            ],
        ]);
    }

    public function occupancyReport(Request $request)
    {
        $query = Property::query();

        // Filter by landlord if provided
        if ($request->has('landlord_id')) {
            $query->where('landlord_id', $request->landlord_id);
        }

        // Total properties
        $totalProperties = $query->count();

        // Occupied properties
        $occupiedProperties = $query->where('available_units', true)->count();

        // Calculate occupancy rate
        $occupancyRate = $totalProperties > 0
            ? round(($occupiedProperties / $totalProperties) * 100, 2)
            : 0;

        return response()->json([
            'success' => true,
            'data' => [
                'total_properties' => $totalProperties,
                'occupied_properties' => $occupiedProperties,
                'occupancy_rate' => $occupancyRate,
            ],
        ]);
    }

    public function maintenanceReport(Request $request)
    {
        $query = MaintenanceRequest::query();

        // Apply filters for date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        // Total maintenance requests
        $totalRequests = $query->count();

        // Completed maintenance requests
        $completedRequests = $query->completed()->count();

        // Pending maintenance requests
        $pendingRequests = $query->pending()->count();

        // Overdue maintenance requests
        $overdueRequests = $query->overdue()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'total_requests' => $totalRequests,
                'completed_requests' => $completedRequests,
                'pending_requests' => $pendingRequests,
                'overdue_requests' => $overdueRequests,
            ],
        ]);
    }
    /**
     * Get users report.
     */
    public function usersReport(Request $request)
    {
        $query = User::query();

        // Apply filters for date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        // Total users
        $totalUsers = $query->count();

        // Users by role
        $usersByRole = $query->selectRaw('role, COUNT(*) as count')
            ->groupBy('role')
            ->get();

        // Users by status
        $usersByStatus = $query->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get();

        // New users by month
        $monthlySignups = $query->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'total_users' => $totalUsers,
                'users_by_role' => $usersByRole,
                'users_by_status' => $usersByStatus,
                'monthly_signups' => $monthlySignups,
            ],
        ]);
    }
}



// <!--

// namespace App\Http\Controllers\Api;

// use App\Http\Controllers\Controller;
// use Illuminate\Http\Request;

// class DashboardController extends Controller
// {
//     /**
//      * Get dashboard statistics.
//      */
//     public function stats()
//     {
//         // Example statistics data
//         $stats = [
//             'total_users' => 100,
//             'total_properties' => 50,
//             'total_tenants' => 30,
//             'total_landlords' => 20,
//         ];

//         return response()->json([
//             'success' => true,
//             'data' => $stats,
//         ]);
//     }

//     /**
//      * Get recent activities.
//      */
//     public function recentActivities()
//     {
//         // Example recent activities data
//         $activities = [
//             ['id' => 1, 'description' => 'User John Doe signed up', 'created_at' => now()],
//             ['id' => 2, 'description' => 'Property "Villa Bonanjo" was added', 'created_at' => now()],
//         ];

//         return response()->json([
//             'success' => true,
//             'data' => $activities,
//         ]);
//     }

//     /**
//      * Get analytics data.
//      */
//     public function analytics()
//     {
//         // Example analytics data
//         $analytics = [
//             'monthly_signups' => [10, 20, 15, 30],
//             'monthly_revenue' => [1000, 2000, 1500, 3000],
//         ];

//         return response()->json([
//             'success' => true,
//             'data' => $analytics,
//         ]);
//     }
// }
//  -->
