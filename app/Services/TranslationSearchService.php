<?php

namespace App\Services;

use App\Contracts\TranslationSearchContract;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class TranslationSearchService implements TranslationSearchContract
{
    public function search(Request $request): LengthAwarePaginator
    {
        // Start with a query builder and select necessary columns
        $query = Translation::query()->select('translations.*');

        // Use eager loading with constraints to reduce queries
        $query->with([
            'language' => function ($query) {
                $query->select('id', 'name', 'code');
            },
            'tags' => function ($query) {
                $query->select('tags.id', 'tags.name');
            }
        ]);

        // Apply filters conditionally
        $this->applyLanguageFilter($query, $request);
        $this->applyTagFilter($query, $request);
        $this->applyKeyFilter($query, $request);
        $this->applyContentFilter($query, $request);

        // Return paginated results with efficient count query
        return $query->paginate(
            $request->input('per_page', 15),
            ['*'],
            'page',
            $request->input('page', 1)
        );
    }

    private function applyLanguageFilter($query, Request $request): void
    {
        if ($request->filled('language')) {
            $query->where('language_id', $request->input('language'));
        }
    }

    private function applyTagFilter($query, Request $request): void
    {
        if ($request->filled('tag')) {
            $tagId = $request->input('tag');

            // Use join instead of whereHas for better performance with large datasets
            $query->join('translation_tag', 'translations.id', '=', 'translation_tag.translation_id')
                ->where('translation_tag.tag_id', $tagId)
                ->distinct('translations.id'); // Prevent duplicate rows
        }
    }

    private function applyKeyFilter($query, Request $request): void
    {
        if ($request->filled('key')) {
            $key = $request->input('key');

            // Use LIKE with wildcards only if necessary
            if (str_contains($key, '%')) {
                $query->where('key', 'LIKE', $key);
            } else {
                // For exact matching or prefix search (more efficient)
                if (str_ends_with($key, '%')) {
                    $query->where('key', 'LIKE', $key);
                } else {
                    $query->where('key', $key);
                }
            }
        }
    }

    private function applyContentFilter($query, Request $request): void
    {
        if ($request->filled('value')) {
            $value = $request->input('value');

            // For text columns, full-text search is more efficient when available
            if (DB::connection()->getDriverName() === 'mysql' && $this->hasFullTextIndex('translations', 'value')) {
                $query->whereRaw("MATCH(value) AGAINST(? IN BOOLEAN MODE)", [$value . '*']);
            } else {
                $query->where('value', 'LIKE', "%{$value}%");
            }
        }
    }

    private function hasFullTextIndex(string $table, string $column): bool
    {
        // This method would check if a FULLTEXT index exists on the table column
        // Implementation is database-specific
        // For a complete implementation, you'd check the database schema

        // Placeholder implementation
        return false;
    }
}
