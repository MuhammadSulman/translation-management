<?php

namespace App\Services;

use App\Models\Translation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class TranslationSearchService
{
    public function search(Request $request): LengthAwarePaginator
    {
        ($query = Translation::query())
            ->with(
                [
                    'language',
                    'tags'
                ]
            );

        $this->applyTagFilter($query, $request);
        $this->applyKeyFilter($query, $request);
        $this->applyContentFilter($query, $request);

        return $query->paginate(15);
    }

    private function applyTagFilter($query, Request $request): void
    {
        if ($request->filled('tag')) {
            $query->whereHas('tags', function (Builder $query) use ($request) {
                $query->where($query->qualifyColumn('id'), $request->input('tag'));
            });
        }
    }

    private function applyKeyFilter($query, Request $request): void
    {
        if ($request->filled('key')) {
            $query->where('key', 'LIKE', "%{$request->input('key')}%");
        }
    }

    private function applyContentFilter($query, Request $request): void
    {
        if ($request->filled('value')) {
            $query->where('value', 'LIKE', "%{$request->input('value')}%");
        }
    }
}
