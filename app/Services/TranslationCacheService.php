<?php

namespace App\Services;

use App\Contracts\TranslationCacheContract;
use App\Models\Translation;
use Illuminate\Support\Facades\Cache;

class TranslationCacheService implements TranslationCacheContract
{
    /**
     * Cache time in seconds (20 minutes)
     */
    protected const CACHE_TTL = 1200;

    /**
     * Get translations for specific languages and tags from cache or database
     *
     * @param array $languages Language IDs
     * @param array $tags Tag IDs
     * @return array Translations formatted as [language_code => [key => value]]
     */
    public function getTranslations(array $languages = [], array $tags = []): array
    {
        $cacheKey = $this->generateCacheKey($languages, $tags);

        // Return from cache if available
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        // Otherwise, fetch from database and cache
        $translations = $this->fetchTranslationsFromDatabase($languages, $tags);
        Cache::put($cacheKey, $translations, self::CACHE_TTL);

        return $translations;
    }

    /**
     * Clear translation cache when translations are updated
     */
    public function clearTranslationCache(): void
    {
        // Get list of cache keys for translations
        $cacheKeys = Cache::get('translation_cache_keys', []);

        // Clear each key
        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        // Clear the list of cache keys itself
        Cache::forget('translation_cache_keys');
    }

    /**
     * Generate a cache key based on the language and tag filters
     */
    public function generateCacheKey(array $languages, array $tags): string
    {
        $key = 'translations';

        if (!blank($languages)) {
            sort($languages);
            $key .= '_lang_' . implode('_', $languages);
        }

        if (!blank($tags)) {
            sort($tags);
            $key .= '_tag_' . implode('_', $tags);
        }

        // Store this key in the list of translation cache keys
        $cacheKeys = Cache::get('translation_cache_keys', []);
        if (!in_array($key, $cacheKeys, true)) {
            $cacheKeys[] = $key;
            Cache::put('translation_cache_keys', $cacheKeys, self::CACHE_TTL);
        }

        return $key;
    }

    /**
     * Fetch translations from database and format them
     */
    public function fetchTranslationsFromDatabase(array $languages, array $tags): array
    {
        $query = Translation::with('language');

        if (!blank($languages)) {
            $query->whereIn('language_id', $languages);
        }

        if (!blank($tags)) {
            $query->whereHas('tags', function ($q) use ($tags) {
                $q->whereIn('tags.id', $tags);
            });
        }

        $results = $query->get();

        // Format results by language code
        $formatted = [];
        foreach ($results as $translation) {
            $langCode = $translation->language->code;
            if (!isset($formatted[$langCode])) {
                $formatted[$langCode] = [];
            }
            $formatted[$langCode][$translation->key] = $translation->value;
        }

        return $formatted;
    }
}
