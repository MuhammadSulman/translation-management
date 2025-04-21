<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LanguageRequest;
use App\Http\Resources\LanguageResource;
use App\Models\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class LanguageController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return LanguageResource::collection(Language::all());
    }

    public function store(LanguageRequest $request): LanguageResource
    {
        $language = Language::create($request->validated());
        return LanguageResource::make($language);
    }

    public function update(LanguageRequest $request, Language $language): LanguageResource
    {
        $language->update($request->validated());
        return LanguageResource::make($language->fresh());
    }

    public function destroy(Language $language): JsonResponse
    {
        $language->delete();
        return response()->json(null, 204);
    }
}
