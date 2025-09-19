<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Requests\Payment\UpdatePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Models\Notification;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $query = Payment::query()->with(['tenant', 'property', 'paymentMethod', 'processedBy']);

        $user = $request->user();

        // Filter by user role
        if ($user->isTenant()) {
            $query->where('tenant_id', $user->id);
        } elseif ($user->isLandlord()) {
            $query->whereHas('property', function ($q) use ($user) {
                $q->where('landlord_id', $user->id);
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by property
        if ($request->has('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        // Filter by tenant
        if ($request->has('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        // Filter by payment type
        if ($request->has('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('due_date', [$request->start_date, $request->end_date]);
        }

        // Filter overdue
        if ($request->has('overdue') && $request->overdue) {
            $query->overdue();
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_reference', 'like', "%{$search}%")
                    ->orWhere('receipt_number', 'like', "%{$search}%")
                    ->orWhereHas('tenant', function ($subQ) use ($search) {
                        $subQ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'due_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $payments = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => PaymentResource::collection($payments),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ]
        ]);
    }

    public function store(StorePaymentRequest $request)
    {
        $this->authorize('create', Payment::class);

        $payment = Payment::create($request->validated());

        // Create notification for tenant
        Notification::createPaymentReminder(
            $payment->tenant_id,
            $payment->id,
            $payment->due_date
        );

        return response()->json([
            'success' => true,
            'message' => 'Payment created successfully',
            'data' => new PaymentResource($payment->load(['tenant', 'property', 'paymentMethod']))
        ], 201);
    }

    public function show(Payment $payment)
    {
        $this->authorize('view', $payment);

        return response()->json([
            'success' => true,
            'data' => new PaymentResource($payment->load(['tenant', 'property', 'paymentMethod', 'processedBy']))
        ]);
    }

    public function update(UpdatePaymentRequest $request, Payment $payment)
    {
        $this->authorize('update', $payment);

        $payment->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Payment updated successfully',
            'data' => new PaymentResource($payment->load(['tenant', 'property', 'paymentMethod']))
        ]);
    }

    public function destroy(Payment $payment)
    {
        $this->authorize('delete', $payment);

        if ($payment->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete completed payment'
            ], 422);
        }

        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment deleted successfully'
        ]);
    }

    public function markAsPaid(Request $request, Payment $payment)
    {
        $this->authorize('update', $payment);

        $request->validate([
            'payment_method_id' => 'required|exists:payment_methods,id',
            'transaction_reference' => 'nullable|string|max:255',
        ]);

        if ($payment->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Payment is already completed'
            ], 422);
        }

        $payment->markAsPaid(
            $request->payment_method_id,
            $request->transaction_reference,
            $request->user()->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Payment marked as paid successfully',
            'data' => new PaymentResource($payment->load(['tenant', 'property', 'paymentMethod', 'processedBy']))
        ]);
    }

    public function pending(Request $request)
    {
        $query = Payment::pending()->with(['tenant', 'property', 'paymentMethod']);

        $user = $request->user();

        if ($user->isTenant()) {
            $query->where('tenant_id', $user->id);
        } elseif ($user->isLandlord()) {
            $query->whereHas('property', function ($q) use ($user) {
                $q->where('landlord_id', $user->id);
            });
        }

        $payments = $query->orderBy('due_date', 'asc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => PaymentResource::collection($payments),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ]
        ]);
    }

    public function overdue(Request $request)
    {
        $query = Payment::overdue()->with(['tenant', 'property', 'paymentMethod']);

        $user = $request->user();

        if ($user->isTenant()) {
            $query->where('tenant_id', $user->id);
        } elseif ($user->isLandlord()) {
            $query->whereHas('property', function ($q) use ($user) {
                $q->where('landlord_id', $user->id);
            });
        }

        $payments = $query->orderBy('due_date', 'asc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => PaymentResource::collection($payments),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ]
        ]);
    }

    public function statistics(Request $request)
    {
        $user = $request->user();
        $query = Payment::query();

        // Apply user-specific filters
        if ($user->isTenant()) {
            $query->where('tenant_id', $user->id);
        } elseif ($user->isLandlord()) {
            $query->whereHas('property', function ($q) use ($user) {
                $q->where('landlord_id', $user->id);
            });
        }

        $stats = [
            'total_revenue' => $query->clone()->completed()->sum('amount'),
            'monthly_revenue' => $query->clone()->completed()
                ->whereMonth('paid_date', now()->month)
                ->whereYear('paid_date', now()->year)
                ->sum('amount'),
            'pending_amount' => $query->clone()->pending()->sum('amount'),
            'overdue_amount' => $query->clone()->overdue()->sum('amount'),
            'total_payments' => $query->clone()->count(),
            'completed_payments' => $query->clone()->completed()->count(),
            'pending_payments' => $query->clone()->pending()->count(),
            'overdue_payments' => $query->clone()->overdue()->count(),
        ];



        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
    public function exportPdf(Request $request)
    {
        // Reuse the query logic from index() for consistency
        $query = Payment::query()->with(['tenant', 'property', 'paymentMethod', 'processedBy']);
        $user = $request->user();

        // Apply role-based filters (same as index)
        if ($user->isTenant()) {
            $query->where('tenant_id', $user->id);
        } elseif ($user->isLandlord()) {
            $query->whereHas('property', function ($q) use ($user) {
                $q->where('landlord_id', $user->id);
            });
        }

        // Apply other filters (same as index: status, property_id, tenant_id, payment_type, date range, overdue, search)
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('property_id')) {
            $query->where('property_id', $request->property_id);
        }
        if ($request->has('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }
        if ($request->has('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('due_date', [$request->start_date, $request->end_date]);
        }
        if ($request->has('overdue') && $request->overdue) {
            $query->overdue();
        }
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_reference', 'like', "%{$search}%")
                    ->orWhere('receipt_number', 'like', "%{$search}%")
                    ->orWhereHas('tenant', function ($subQ) use ($search) {
                        $subQ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }


        // Sort (same as index)
        $sortBy = $request->get('sort_by', 'due_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Fetch all data (no paginate for export)
        $payments = $query->get();

        // Load the Blade view and generate PDF
        $pdf = Pdf::loadView('exports.payments-pdf', ['payments' => $payments, 'user' => $user]);

        // Return as streamed download
        return $pdf->download('payments.pdf'); // Or use ->stream() for inline view, but download is better for API
    }

    public function downloadReceipt(Request $request, $paymentId)
    {
        $payment = Payment::with(['tenant', 'property', 'paymentMethod'])->findOrFail($paymentId);

        $user = $request->user();

        // Authorization: Ensure user can access this payment
        if ($user->isTenant() && $payment->tenant_id !== $user->id) {
            abort(403, 'Unauthorized');
        } elseif ($user->isLandlord()) {
            if (!$payment->property || $payment->property->landlord_id !== $user->id) {
                abort(403, 'Unauthorized');
            }
        }
        // For admins, allow access (no check needed)

        $pdf = Pdf::loadView('exports.payment-receipt', [
            'payment' => $payment,
            'user' => $user,
        ]);

        return $pdf->download("receipt-{$payment->id}.pdf");
    }

    public function exportCsv(Request $request)
    {
        $query = Payment::query()->with(['tenant', 'property', 'paymentMethod', 'processedBy']);
        $user = $request->user();

        // Role-based filters
        if ($user->isTenant()) {
            $query->where('tenant_id', $user->id);
        } elseif ($user->isLandlord()) {
            $query->whereHas('property', function ($q) use ($user) {
                $q->where('landlord_id', $user->id);
            });
        }

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('property_id')) {
            $query->where('property_id', $request->property_id);
        }
        if ($request->has('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }
        if ($request->has('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('due_date', [$request->start_date, $request->end_date]);
        }
        if ($request->has('overdue') && $request->overdue) {
            $query->overdue();
        }
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_reference', 'like', "%{$search}%")
                    ->orWhere('receipt_number', 'like', "%{$search}%")
                    ->orWhereHas('tenant', function ($subQ) use ($search) {
                        $subQ->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'due_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Fetch data
        $payments = $query->get();

        // Generate CSV
        return Excel::download(new PaymentsExport($payments), 'payments.csv');
    }
}
