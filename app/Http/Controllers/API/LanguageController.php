<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\LanguageRequest;
use App\Http\Resources\LanguageResource;
use App\Models\Language;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use OpenApi\Annotations as OA;

class LanguageController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/languages",
     *     summary="List all languages",
     *     tags={"Languages"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of languages",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Language")
     *         )
     *     )
     * )
     */
    public function index(): AnonymousResourceCollection
    {
        return LanguageResource::collection(Language::all());
    }

    /**
     * @OA\Post(
     *     path="/api/languages",
     *     summary="Create a new language",
     *     tags={"Languages"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","code"},
     *             @OA\Property(property="name", type="string", example="English"),
     *             @OA\Property(property="code", type="string", example="en")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Language created",
     *         @OA\JsonContent(ref="#/components/schemas/Language")
     *     )
     * )
     */
    public function store(LanguageRequest $request): LanguageResource
    {
        $language = Language::create($request->validated());
        return LanguageResource::make($language);
    }

    /**
     * @OA\Put(
     *     path="/api/languages/{id}",
     *     summary="Update a language",
     *     tags={"Languages"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","code"},
     *             @OA\Property(property="name", type="string", example="English"),
     *             @OA\Property(property="code", type="string", example="en")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Language updated",
     *         @OA\JsonContent(ref="#/components/schemas/Language")
     *     )
     * )
     */
    public function update(LanguageRequest $request, Language $language): LanguageResource
    {
        $language->update($request->validated());
        return LanguageResource::make($language->fresh());
    }

    /**
     * @OA\Delete(
     *     path="/api/languages/{id}",
     *     summary="Delete a language",
     *     tags={"Languages"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Language deleted"
     *     )
     * )
     */
    public function destroy(Language $language): JsonResponse
    {
        $language->delete();
        return response()->json(null, 204);
    }
}
