<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Language extends Model
{
    use HasFactory;

    protected $table = 'languages';

    protected $fillable = ['code', 'name'];

    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class);
    }

}
