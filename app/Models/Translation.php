<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Translation extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'key',
        'value',
        'language_id',
    ];

    /**
     * Get the language that owns the translation.
     */
    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class);
    }

    /**
     * The tags that belong to the translation.
     */
    public function tags(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                Tag::class,
                'translation_tag'
            );
    }

    /**
     * Scope a query to filter by key.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $key
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereKey($query, $key)
    {
        return $query->where('key', $key);
    }

    /**
     * Scope a query to filter by language.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|array $languageId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereLanguage($query, $languageId)
    {
        return $query->whereIn('language_id', (array) $languageId);
    }

    /**
     * Scope a query to filter by value containing given string.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereValueContains($query, $value)
    {
        return $query->where('value', 'like', "%{$value}%");
    }

    /**
     * Scope a query to filter by tag.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|array $tagIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereHasTag($query, $tagIds)
    {
        return $query->whereHas('tags', function ($query) use ($tagIds) {
            $query->whereIn('tags.id', (array) $tagIds);
        });
    }
}
