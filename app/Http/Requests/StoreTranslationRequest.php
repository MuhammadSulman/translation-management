<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key' => 'required|string',
            'value' => 'required|string',
            'language_id' => 'required|exists:languages,id',
            'tags' => 'array'
        ];
    }
}
