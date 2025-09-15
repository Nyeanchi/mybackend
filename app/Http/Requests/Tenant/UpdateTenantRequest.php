<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantRequest extends FormRequest
{
    public function authorize()
    {
        // Authorize only if the user has permission to update a tenant
        return $this->user()->can('update', $this->route('tenant'));
    }

    public function rules()
    {
        return [
            'user_id' => 'nullable|exists:users,id', // Make user_id optional
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $this->route('tenant')->user_id,
            'password' => 'nullable|string|min:8',
            'phone' => 'sometimes|string|max:20|unique:users,phone,' . $this->route('tenant')->user_id,
            'property_id' => 'nullable|exists:properties,id',
            'unit_number' => 'nullable|string|max:50',
            'lease_start' => 'nullable|date',
            'lease_end' => 'nullable|date|after_or_equal:lease_start',
            'rent_amount' => 'nullable|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:active,inactive,suspended',
            'move_in_date' => 'nullable|date',
            'move_out_date' => 'nullable|date|after:move_in_date',
            'notes' => 'nullable|string|max:2000',
        ];
    }

    public function messages()
    {
        return [
            'user_id.exists' => 'The selected user does not exist.',
            'email.unique' => 'The email must be unique.',
            'phone.unique' => 'The phone number must be unique.',
            'lease_end.after_or_equal' => 'The lease end date must be after or equal to the lease start date.',
            'move_out_date.after' => 'The move-out date must be after the move-in date.',
            'notes.max' => 'The notes must not exceed 2000 characters.',
        ];
    }
}
