<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LanguageRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $language = $this->route('language');

        return [
            'code' => [
                'required',
                'max:10',
                Rule::unique('languages', 'code')->ignore($language?->id),
            ],
            'name' => [
                'required',
                'max:255'
            ],
        ];
    }
}
