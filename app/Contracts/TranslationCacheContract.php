<?php

namespace App\Contracts;

interface TranslationCacheContract
{
    public function getTranslations(array $languages = [], array $tags = []): array;

    public function clearTranslationCache(): void;

    public function generateCacheKey(array $languages, array $tags): string;

    public function fetchTranslationsFromDatabase(array $languages, array $tags): array;
}
