<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    /**
     * Create a new payment method.
     */
    public function createPaymentMethod(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:orange_money,mtn_momo,bank_transfer,cash',
            'description' => 'nullable|string',
            'is_active' => 'required|boolean',
            'config' => 'nullable|array',
        ]);

        $paymentMethod = PaymentMethod::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Payment method created successfully.',
            'data' => $paymentMethod,
        ], 201);
    }

    /**
     * Get all payment methods.
     */
    public function getPaymentMethods()
    {
        $paymentMethods = PaymentMethod::all();

        return response()->json([
            'success' => true,
            'data' => $paymentMethods,
        ]);
    }

    /**
     * Update a payment method.
     */
    public function updatePaymentMethod(Request $request, PaymentMethod $paymentMethod)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:orange_money,mtn_momo,bank_transfer,cash',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
            'config' => 'nullable|array',
        ]);

        $paymentMethod->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Payment method updated successfully.',
            'data' => $paymentMethod,
        ]);
    }

    /**
     * Delete a payment method.
     */
    public function deletePaymentMethod(PaymentMethod $paymentMethod)
    {
        $paymentMethod->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment method deleted successfully.',
        ]);
    }
}
