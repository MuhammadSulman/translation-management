<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Translation extends Model
{
    protected $table = 'translations';
    protected $fillable = ['key', 'value', 'language_id'];

    public function language(): BelongsTo
    {
        return $this
            ->belongsTo(
                Language::class,
                'language_id',
                'id',
                'language'
            );
    }

    public function tags(): BelongsToMany
    {
        return $this
            ->belongsToMany(
                Tag::class,
                'translation_tag',
                'translation_id',
                'tag_id',
                'id',
                'id',
                'tags'
            );
    }
}
