<?php

namespace App\Http\Requests\Property;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePropertyRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user()->can('update', $this->route('property'));
    }

    public function rules()
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'type' => ['sometimes', 'required', Rule::in(['apartment', 'house', 'studio', 'commercial','villa', 'office', 'others'])],
            'address' => 'sometimes|required|string|max:500',
            'city_id' => 'sometimes|required|exists:cities,id',
            'total_units' => 'sometimes|required|integer|min:1|max:1000',
            'available_units' => 'sometimes|required|integer|min:0',
            'rent_amount' => 'sometimes|required|numeric|min:0',
            'deposit_amount' => 'sometimes|nullable|numeric|min:0',
            'currency' => 'sometimes|nullable|string|in:FCFA,EUR,USD',
            'description' => 'sometimes|nullable|string|max:2000',
            'amenities' => 'sometimes|nullable|array',
            'amenities.*' => 'string|max:255',
            'images' => 'sometimes|nullable|array',
            'images.*' => 'string|max:255',
            'status' => ['sometimes', 'required', Rule::in(['active', 'inactive', 'maintenance'])],
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
            'total_units.required' => 'Le nombre total d\'unités est obligatoire.',
            'total_units.min' => 'Le nombre total d\'unités doit être au moins 1.',
            'available_units.required' => 'Le nombre d\'unités disponibles est obligatoire.',
            'rent_amount.required' => 'Le montant du loyer est obligatoire.',
            'rent_amount.min' => 'Le montant du loyer doit être positif.',
            'deposit_amount.nullable' => 'La devise est optionelle.',
            'currency.in' => 'La devise doit être FCFA, EUR ou USD.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Ensure available_units doesn't exceed total_units
            if ($this->has('available_units') && $this->has('total_units')) {
                if ($this->available_units > $this->total_units) {
                    $validator->errors()->add('available_units', 'Le nombre d\'unités disponibles ne peut pas dépasser le total.');
                }
            }
        });
    }
}
