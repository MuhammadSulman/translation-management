<?php

namespace App\Contracts;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

interface TranslationSearchContract
{
    public function search(Request $request): LengthAwarePaginator;
}
