<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('update', $this->route('user'));
    }

    public function rules()
    {
        $userId = $this->route('user')->id;

        return [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|nullable|string|max:255',
            'email' => ['sometimes', 'required', 'email', Rule::unique('users')->ignore($userId)],
            'phone' => ['sometimes', 'required', 'string', 'max:20', Rule::unique('users')->ignore($userId)],
            'password' => 'sometimes|nullable|string|min:8',
            'role' => ['sometimes', 'required', Rule::in(['landlord', 'tenant', 'admin'])],
            'status' => ['sometimes', 'required', Rule::in(['active', 'inactive', 'suspended'])],
            'city_id' => 'sometimes|required|exists:cities,id',
            'address' => 'sometimes|nullable|string|max:500',
            'date_of_birth' => 'sometimes|nullable|date|before:today',
            'emergency_contact_name' => 'sometimes|nullable|string|max:255',
            'emergency_contact_phone' => 'sometimes|nullable|string|max:20',
            'avatar' => 'sometimes|nullable|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.nullable' => 'Le nom est optionelle.',
            'email.required' => 'L\'adresse email est obligatoire.',
            'email.email' => 'L\'adresse email doit être valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée.',
            'phone.required' => 'Le numéro de téléphone est obligatoire.',
            'phone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'role.required' => 'Le rôle est obligatoire.',
            'role.in' => 'Le rôle doit être admin, propriétaire ou locataire.',
            'city_id.required' => 'La ville est obligatoire.',
            'city_id.exists' => 'La ville sélectionnée n\'existe pas.',
            'address.nullable' => 'L\'adresse est optionelle.',
            'date_of_birth.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
        ];
    }
}