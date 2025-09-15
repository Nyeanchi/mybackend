<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->role === 'admin' || $this->notification->recipient_id === auth()->user()->id;
    }

    public function rules()
    {
        return [];
    }
}
