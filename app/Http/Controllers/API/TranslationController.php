<?php

namespace App\Http\Controllers\API;

use App\Contracts\TranslationCacheContract;
use App\Contracts\TranslationSearchContract;
use App\Http\Controllers\Controller;
use App\Http\Requests\TranslationRequest;
use App\Http\Resources\TranslationResource;
use App\Models\Translation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TranslationController extends Controller
{
    public function __construct(
        private TranslationSearchContract $searchService,
        private TranslationCacheContract $cacheService
    )
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $translations = $this->searchService->search($request);

        return TranslationResource::collection($translations);
    }

    public function store(TranslationRequest $request): TranslationResource
    {
        $requestPayload = $request->validated();

        $translation = Translation::create($requestPayload);

        if (isset($requestPayload['tags'])) {
            $translation->tags()->sync($requestPayload['tags']);
        }

        $translation->load(['language', 'tags']);

        // Clear cache when data is modified
        $this->cacheService->clearTranslationCache();

        return TranslationResource::make($translation);
    }

    public function update(TranslationRequest $request, Translation $translation): TranslationResource
    {
        $requestPayload = $request->validated();

        $translation->update($requestPayload);

        if (isset($requestPayload['tags'])) {
            $translation->tags()->sync($requestPayload['tags']);
        }

        $translation->fresh();
        $translation->load(['language', 'tags']);

        // Clear cache when data is modified
        $this->cacheService->clearTranslationCache();

        return TranslationResource::make($translation);
    }

    public function destroy(Translation $translation): JsonResponse
    {
        $translation->delete();

        // Clear cache when data is modified
        $this->cacheService->clearTranslationCache();

        return response()->json(null, 204);
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        // Get input parameters
        $languages = $request->input('languages', []);
        $tags = $request->input('tags', []);

        // Generate filename
        $filename = 'translations_export_' . now()->format('Ymd_His') . '.json';

        // Use the cache service to get translations
        $translations = $this->cacheService->getTranslations($languages, $tags);

        // Stream the JSON response
        return response()->streamDownload(function () use ($translations) {
            echo json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
