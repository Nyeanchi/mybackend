<?php

namespace App\Http\Requests\Property;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePropertyRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('create', \App\Models\Property::class);
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(['apartment', 'house', 'studio', 'commercial', 'villa', 'office', 'others'])],
            'address' => 'required|string|max:500',
            'city_id' => 'required|exists:cities,id',
            'landlord_id' => 'sometimes|exists:users,id',
            'total_units' => 'required|integer|min:1|max:1000',
            'available_units' => 'required|integer|min:0|lte:total_units',
            'rent_amount' => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|in:FCFA,EUR,USD',
            'description' => 'nullable|string|max:2000',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string|max:255',
            'images' => 'nullable|array',
            'images.*' => 'string|max:255',
            'status' => ['nullable', Rule::in(['active', 'inactive', 'maintenance'])],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Le nom de la propriété est obligatoire.',
            'type.required' => 'Le type de propriété est obligatoire.',
            'type.in' => 'Le type de propriété doit être appartement, maison, studio ou commercial.',
            'address.required' => 'L\'adresse est obligatoire.',
            'city_id.required' => 'La ville est obligatoire.',
            'city_id.exists' => 'La ville sélectionnée n\'existe pas.',
            'landlord_id.exists' => 'Le propriétaire sélectionné n\'existe pas.',
            'total_units.required' => 'Le nombre total d\'unités est obligatoire.',
            'total_units.min' => 'Le nombre total d\'unités doit être au moins 1.',
            'available_units.required' => 'Le nombre d\'unités disponibles est obligatoire.',
            'available_units.lte' => 'Le nombre d\'unités disponibles ne peut pas dépasser le total.',
            'rent_amount.required' => 'Le montant du loyer est obligatoire.',
            'rent_amount.min' => 'Le montant du loyer doit être positif.',
            'deposit_amount.nullable' => 'La devise est optionelle.',
            'currency.in' => 'La devise doit être FCFA, EUR ou USD.',
        ];
    }

    public function prepareForValidation()
    {
        // Set default values
        if (!$this->has('available_units')) {
            $this->merge(['available_units' => $this->total_units]);
        }

        if (!$this->has('currency')) {
            $this->merge(['currency' => 'FCFA']);
        }

        if (!$this->has('status')) {
            $this->merge(['status' => 'active']);
        }
    }
}
