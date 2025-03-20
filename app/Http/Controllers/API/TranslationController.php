<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\TranslationRequest;
use App\Http\Resources\TranslationResource;
use App\Models\Translation;
use App\Services\TagService;
use App\Services\TranslationSearchService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TranslationController extends Controller
{
    public function __construct(
        private TranslationSearchService $searchService
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

        $translation->tags()->sync($requestPayload['tags']);

        $translation->load(
            [
                'language',
                'tags'
            ]
        );

        return TranslationResource::make($translation);
    }

    public function update(TranslationRequest $request, Translation $translation): TranslationResource
    {
        $requestPayload = $request->validated();

        $translation->update($requestPayload);

        $translation->tags()->sync($requestPayload['tags']);

        $translation->fresh();

        $translation->load(
            [
                'language',
                'tags'
            ]
        );

        return TranslationResource::make($translation);
    }

    public function destroy(Translation $translation): JsonResponse
    {
        $translation->delete();
        return response()->json(null, 204);
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        // Get input parameters
        $languages = $request->input('languages', []);
        $tags = $request->input('tags', []);

        // Build the query efficiently
        $query = DB::table('translations')
            ->join('languages', 'translations.language_id', '=', 'languages.id');

        // Filter by languages if provided
        if (!blank($languages)) {
            $query->whereIn('languages.id', (array) $languages);
        }

        // Filter by tags if provided
        if (!blank($tags)) {
            $query->join('translation_tag', 'translations.id', '=', 'translation_tag.translation_id')
                ->whereIn('translation_tag.tag_id', (array) $tags);
        }

        // Define the grouped selection
        $query->selectRaw('languages.code, GROUP_CONCAT(JSON_OBJECT("key", translations.key, "value", translations.value)) as translations')
            ->groupBy('languages.code')
            ->orderBy('languages.code');

        // Generate filename
        $filename = 'translations_export_' . now()->format('Ymd_His') . '.json';

        // Stream the JSON response
        return response()->streamDownload(function () use ($query) {
            // Open JSON structure
            echo "{\n";

            // Stream results in chunks
            $firstLanguage = true;
            $query->chunk(1000, function ($languageGroups) use (&$firstLanguage) {
                foreach ($languageGroups as $group) {
                    dd($group);

                    if (!$firstLanguage) {
                        echo ",\n"; // Add comma between language groups
                    }
                    $firstLanguage = false;

                    // Handle empty or invalid translations
                    if (empty($group->translations)) {
                        $translations = [];
                    } else {
                        try {
                            $translations = json_decode('[' . $group->translations . ']', true, 512, JSON_THROW_ON_ERROR);
                        } catch (\JsonException $e) {
                            // Log error and use empty array as fallback
                            \Log::error("JSON decode failed for language {$group->code}: " . $e->getMessage(), ['translations' => $group->translations]);
                            $translations = [];
                        }
                    }

                    $formatted = json_encode(
                        array_column($translations, 'value', 'key'),
                        JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                    );

                    // Output language block
                    echo sprintf('    "%s": %s', $group->code, $formatted);
                }
            });

            // Close JSON structure
            echo "\n}";
        }, $filename, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
