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
use OpenApi\Annotations as OA;


class TranslationController extends Controller
{
    public function __construct(
        private TranslationSearchContract $searchService,
        private TranslationCacheContract $cacheService
    )
    {
    }

    /**
     * @OA\Get(
     *     path="/api/translations",
     *     summary="List translations with filters",
     *     tags={"Translations"},
     *     security={"sanctum"={}},
     *     @OA\Parameter(name="language_id", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="tags[]", in="query", required=false, @OA\Schema(type="array", @OA\Items(type="integer"))),
     *     @OA\Parameter(name="search", in="query", required=false, @OA\Schema(type="string")),
     *     @OA\Parameter(name="per_page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Parameter(name="page", in="query", required=false, @OA\Schema(type="integer")),
     *     @OA\Response(
     *         response=200,
     *         description="List of translations",
     *         @OA\JsonContent(type="array", @OA\Items(ref="#/components/schemas/Translation"))
     *     )
     * )
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $translations = $this->searchService->search($request);

        return TranslationResource::collection($translations);
    }

    /**
     * @OA\Post(
     *     path="/api/translations",
     *     summary="Create a new translation",
     *     tags={"Translations"},
     *     security={"sanctum"={}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"key","value","language_id"},
     *             @OA\Property(property="key", type="string", example="greeting.hello"),
     *             @OA\Property(property="value", type="string", example="Hello"),
     *             @OA\Property(property="language_id", type="integer", example=1),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="integer"), example={1,2})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Translation created",
     *         @OA\JsonContent(ref="#/components/schemas/Translation")
     *     )
     * )
     */
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

    /**
     * @OA\Put(
     *     path="/api/translations/{id}",
     *     summary="Update a translation",
     *     tags={"Translations"},
     *     security={"sanctum"={}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"key","value","language_id"},
     *             @OA\Property(property="key", type="string", example="greeting.hello"),
     *             @OA\Property(property="value", type="string", example="Hello"),
     *             @OA\Property(property="language_id", type="integer", example=1),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="integer"), example={1,2})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Translation updated",
     *         @OA\JsonContent(ref="#/components/schemas/Translation")
     *     )
     * )
     */
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

    /**
     * @OA\Delete(
     *     path="/api/translations/{id}",
     *     summary="Delete a translation",
     *     tags={"Translations"},
     *     security={"sanctum"={}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=204, description="Translation deleted")
     * )
     */
    public function destroy(Translation $translation): JsonResponse
    {
        $translation->delete();

        // Clear cache when data is modified
        $this->cacheService->clearTranslationCache();

        return response()->json(null, 204);
    }

    /**
     * @OA\Get(
     *     path="/api/translations/export",
     *     summary="Export translations as JSON (filtered by languages and tags)",
     *     tags={"Translations"},
     *     security={"sanctum"={}},
     *     @OA\Parameter(
     *         name="languages[]",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="array", @OA\Items(type="integer")),
     *         description="Array of language IDs"
     *     ),
     *     @OA\Parameter(
     *         name="tags[]",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="array", @OA\Items(type="integer")),
     *         description="Array of tag IDs"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Exported translations JSON file",
     *         @OA\MediaType(
     *             mediaType="application/json"
     *         )
     *     )
     * )
     */
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
            echo json_encode($translations, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
