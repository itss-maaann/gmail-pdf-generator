<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SimulateConversationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_email' => ['required', 'email'],
            'to_email'   => ['required', 'email'],
        ];
    }
}
