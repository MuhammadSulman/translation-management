<?php

namespace App\Http\Resources;

use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Translation
 */
class TranslationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->getKey(),
            'key' => $this->key,
            'value' => $this->value,
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'language' => LanguageResource::make($this->whenLoaded('language'))
        ];
    }
}
