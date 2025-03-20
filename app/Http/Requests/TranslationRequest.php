<?php

namespace App\Http\Requests;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TranslationRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $translation = $this->route('translation');

        return [
            'key' => [
                'required',
                Rule::unique('translations')
                    ->where(function (Builder $query) {
                        return $query->where('language_id', $this->input('language_id'));
                    })
                ->ignore($translation?->getKey()),
            ],
            'value' => [
                'required'
            ],
            'language_id' => [
                'required',
                'exists:languages,id'
            ],
            'tags' => [
                'sometimes',
                'array',
                'exists:tags,id'
            ]
        ];
    }
}
