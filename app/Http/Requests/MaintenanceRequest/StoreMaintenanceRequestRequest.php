<?php

namespace App\Http\Requests\MaintenanceRequest;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMaintenanceRequestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Allow tenants to create maintenance requests for themselves
        // Allow landlord to create maintenance requests for any tenant
        $user = $this->user();
        return $user->role === 'tenant' || $user->role === 'tenant';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'property_id' => ['required', 'exists:properties,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category' => ['required', Rule::in(['plumbing', 'electrical', 'structural', 'appliance', 'other'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high', 'urgent'])],
            'tenant_id' => ['nullable', 'exists:users,id', Rule::requiredIf($this->user()->role === 'tenant')],
            'images' => ['nullable', 'array'],
            'images.*' => ['string'], // Assuming images are stored as file paths or URLs
        ];
    }
}
