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
        $user = $this->user();
        return in_array($user->role, ['tenant', 'landlord']);
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

            // Tenant ID logic:
            'tenant_id' => $this->user()->role === 'tenant'
                ? ['nullable'] // tenant_id will be auto-set in controller
                : ['required', 'exists:users,id'],

            'images' => ['nullable', 'array'],
            'images.*' => ['string'],
        ];
    }
}
