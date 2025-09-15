<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('update', $this->route('payment'));
    }

    public function rules()
    {
        return [
            'payment_method_id' => 'sometimes|nullable|exists:payment_methods,id',
            'amount' => 'sometimes|required|numeric|min:0',
            'currency' => 'sometimes|required|string|in:FCFA,EUR,USD',
            'type' => ['sometimes', 'required', Rule::in(['rent', 'deposit', 'utilities', 'maintenance', 'late_fee', 'other'])],
            'payment_period' => 'sometimes|required|string|max:50',
            'due_date' => 'sometimes|required|date',
            'paid_date' => 'sometimes|nullable|date',
            'status' => ['sometimes', 'required', Rule::in(['pending', 'completed', 'cancelled', 'failed'])],
            'transaction_reference' => 'sometimes|nullable|string|max:255',
            'receipt_number' => 'sometimes|nullable|string|max:255',
            'notes' => 'sometimes|nullable|string|max:1000',
            'late_fee' => 'sometimes|nullable|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'amount.required' => 'Le montant est obligatoire.',
            'amount.min' => 'Le montant doit être positif.',
            'currency.required' => 'La devise est obligatoire.',
            'currency.in' => 'La devise doit être FCFA, EUR ou USD.',
            'type.required' => 'Le type de paiement est obligatoire.',
            'payment_period.required' => 'La période de paiement est obligatoire.',
            'due_date.required' => 'La date d\'échéance est obligatoire.',
            'due_date.date' => 'La date d\'échéance doit être une date valide.',
            'paid_date.date' => 'La date de paiement doit être une date valide.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $payment = $this->route('payment');

            // Don't allow changing core details for completed payments
            if ($payment && $payment->status === 'completed') {
                $restrictedFields = ['amount', 'tenant_id', 'property_id', 'type'];
                foreach ($restrictedFields as $field) {
                    if ($this->has($field)) {
                        $validator->errors()->add($field, 'Ce champ ne peut pas être modifié pour un paiement complété.');
                    }
                }
            }

            // If status is being changed to completed, ensure paid_date is set
            if ($this->has('status') && $this->status === 'completed' && !$this->paid_date) {
                $this->merge(['paid_date' => now()]);
            }
        });
    }
}
