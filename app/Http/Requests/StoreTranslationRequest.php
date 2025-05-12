<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTranslationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize()
    {
        return true; // Allow all users; change if needed
    }

    public function rules()
    {
        return [
            'key' => 'required|string',
            'locale' => 'required|string',
            'value' => 'required|string',
            'context' => 'required|string',
        ];
    }
}
