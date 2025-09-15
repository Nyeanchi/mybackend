<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', \App\Models\Payment::class);
    }

    public function rules()
    {
        return [
            'tenant_id' => 'required|exists:users,id',
            'property_id' => 'required|exists:properties,id',
            'payment_method_id' => 'nullable|exists:payment_methods,id',
            'amount' => 'required|numeric|min:0',
            'currency' => 'required|string|in:FCFA,EUR,USD',
            'type' => ['required', Rule::in(['rent', 'deposit', 'utilities', 'maintenance', 'late_fee', 'other'])],
            'payment_period' => 'required|string|max:50',
            'due_date' => 'required|date',
            'paid_date' => 'nullable|date',
            'status' => ['nullable', Rule::in(['pending', 'completed', 'cancelled', 'failed'])],
            'transaction_reference' => 'nullable|string|max:255',
            'receipt_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'late_fee' => 'nullable|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'tenant_id.required' => 'Le locataire est obligatoire.',
            'tenant_id.exists' => 'Le locataire sélectionné n\'existe pas.',
            'property_id.required' => 'La propriété est obligatoire.',
            'property_id.exists' => 'La propriété sélectionnée n\'existe pas.',
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

    public function prepareForValidation()
    {
        // Set default values
        if (!$this->has('currency')) {
            $this->merge(['currency' => 'FCFA']);
        }

        if (!$this->has('status')) {
            $this->merge(['status' => 'pending']);
        }

        if (!$this->has('late_fee')) {
            $this->merge(['late_fee' => 0]);
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validate that tenant belongs to the property
            if ($this->tenant_id && $this->property_id) {
                $tenant = \App\Models\Tenant::where('user_id', $this->tenant_id)
                    ->where('property_id', $this->property_id)
                    ->first();

                if (!$tenant) {
                    $validator->errors()->add('tenant_id', 'Le locataire ne réside pas dans cette propriété.');
                }
            }
        });
    }
}
