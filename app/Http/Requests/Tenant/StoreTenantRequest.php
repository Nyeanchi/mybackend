<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreTenantRequest extends FormRequest
{
    public function authorize()
    {
        // Authorize only if the user has permission to create a tenant
        return $this->user()->can('create', \App\Models\Tenant::class);
    }

    public function rules()
    {
        return [
            'user_id' => 'nullable|exists:users,id', // Make user_id optional
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255', // Required if user_id is not provided
            'email' => 'required|email|unique:users,email', // Required if user_id is not provided
            'password' => 'required|string|min:8', // Required if user_id is not provided
            'phone' => 'required|string|max:20|unique:users,phone',
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
            'name.required_without' => 'The name is required when user_id is not provided.',
            'email.required_without' => 'The email is required when user_id is not provided.',
            'email.unique' => 'The email must be unique.',
            'password.required_without' => 'The password is required when user_id is not provided.',
            'property_id.required' => 'The tenant must be associated with a property.',
            'property_id.exists' => 'The selected property does not exist.',
            'unit_number.required' => 'The unit number is required.',
            'unit_number.max' => 'The unit number must not exceed 50 characters.',
            'lease_start.required' => 'The lease start date is required.',
            'lease_start.before_or_equal' => 'The lease start date must be before or equal to the lease end date.',
            'lease_end.required' => 'The lease end date is required.',
            'lease_end.after_or_equal' => 'The lease end date must be after or equal to the lease start date.',
            'rent_amount.required' => 'The monthly rent is required.',
            'rent_amount.min' => 'The monthly rent must be a positive value.',
            'deposit_amount.min' => 'The deposit amount must be a positive value.',
            'status.required' => 'The tenant status is required.',
            'status.in' => 'The status must be either active or inactive.',
            'move_out_date.after' => 'The move-out date must be after the move-in date.',
            'notes.max' => 'The notes must not exceed 2000 characters.',
        ];
    }
}
