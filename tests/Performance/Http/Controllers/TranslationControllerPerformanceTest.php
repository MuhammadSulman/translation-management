<?php

namespace Tests\Performance\Http\Controllers;

use App\Models\Language;
use App\Models\Tag;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TranslationControllerPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create some test data
        $this->languages = Language::factory()->count(3)->create();
        $this->tags = Tag::factory()->count(5)->create();

        // Mock the cache service if needed
        // $this->mock(TranslationCacheContract::class);
    }

    protected function createUniqueTranslations($count)
    {
        $translations = [];
        $timestamp = now()->timestamp;

        for ($i = 0; $i < $count; $i++) {
            $translations[] = [
                'key' => 'key.'.$timestamp.'.'.$i,
                'value' => 'Value '.$i,
                'language_id' => $this->languages->random()->id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Bulk insert for better performance
        DB::table('translations')->insert($translations);

        // Attach tags to each translation
        $insertedTranslations = DB::table('translations')
            ->orderBy('id', 'desc')
            ->take($count)
            ->get();

        $tagRelations = [];
        foreach ($insertedTranslations as $translation) {
            $randomTags = $this->tags->random(2);
            foreach ($randomTags as $tag) {
                $tagRelations[] = [
                    'translation_id' => $translation->id,
                    'tag_id' => $tag->id,
                ];
            }
        }

        DB::table('translation_tag')->insert($tagRelations);
    }
    /** @test */
    public function indexPerformanceWithSmallDataset(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Translation::factory()
            ->count(50)
            ->create()
            ->each(function ($translation) {
                $translation->tags()->attach($this->tags->random(2));
            });

        $start = microtime(true);

        $response = $this->getJson('/api/translations');

        $duration = microtime(true) - $start;

        $response->assertStatus(200);

        fwrite(STDERR, "\nIndex with small dataset (50 records): " . round($duration * 1000, 2) . " ms\n");
        $this->assertLessThan(200, $duration * 1000);
    }


    /** @test */
    public function indexPerformanceWithLargeDataset(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Use the existing createUniqueTranslations method
        $this->createUniqueTranslations(1000);

        $start = microtime(true);

        $response = $this->getJson('/api/translations');

        $duration = microtime(true) - $start;

        $response->assertStatus(200);

        fwrite(STDERR, "\nIndex with large dataset (1000 records): " . round($duration * 1000, 2) . " ms\n");
        $this->assertLessThan(500, $duration * 1000);
    }

    /** @test */
    public function indexPerformanceWithFilters(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create translations
        $translations = [];
        $timestamp = now()->timestamp;

        // Create 500 translations with the word "test" in value for search filter
        for ($i = 0; $i < 500; $i++) {
            $translations[] = [
                'key' => 'filter.key.'.$timestamp.'.'.$i,
                'value' => 'Test value '.$i, // Include "test" for search
                'language_id' => $this->languages->first()->id, // Use first language for filter
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Bulk insert
        DB::table('translations')->insert($translations);

        // Attach first tag to each translation for filter
        $insertedTranslations = DB::table('translations')
            ->where('key', 'like', 'filter.key.'.$timestamp.'%')
            ->get();

        $tagRelations = [];
        foreach ($insertedTranslations as $translation) {
            $tagRelations[] = [
                'translation_id' => $translation->id,
                'tag_id' => $this->tags->first()->id, // Use first tag for filter
            ];
        }

        DB::table('translation_tag')->insert($tagRelations);

        $start = microtime(true);

        $response = $this->getJson('/api/translations?' . http_build_query([
                'language_id' => $this->languages->first()->id,
                'tags' => [$this->tags->first()->id],
                'search' => 'test'
            ]));

        $duration = microtime(true) - $start;

        $response->assertStatus(200);

        fwrite(STDERR, "\nIndex with filters: " . round($duration * 1000, 2) . " ms\n");
        $this->assertLessThan(300, $duration * 1000);
    }

    /** @test */
    public function storePerformance(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = [
            'key' => 'test.key.' . time(),
            'value' => 'Test value',
            'language_id' => $this->languages->first()->id,
            'tags' => $this->tags->take(2)->pluck('id')->toArray()
        ];

        $start = microtime(true);

        $response = $this->postJson('/api/translations', $data);

        $duration = microtime(true) - $start;

        $response->assertStatus(201);

        fwrite(STDERR, "\nStore operation: " . round($duration * 1000, 2) . " ms\n");
        $this->assertLessThan(300, $duration * 1000);
    }

    /** @test */
    public function updatePerformance(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $translation = Translation::factory()->create();
        $translation->tags()->attach($this->tags->random(2));

        $data = [
            'key' => 'updated.key.' . time(),
            'value' => 'Updated value',
            'language_id' => $translation->language_id,
            'tags' => $this->tags->take(3)->pluck('id')->toArray()
        ];

        $start = microtime(true);

        $response = $this->putJson("/api/translations/{$translation->id}", $data);

        $duration = microtime(true) - $start;

        $response->assertStatus(200);

        fwrite(STDERR, "\nUpdate operation: " . round($duration * 1000, 2) . " ms\n");
        $this->assertLessThan(300, $duration * 1000);
    }

    /** @test */
    public function destroyPerformance(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $translation = Translation::factory()->create();
        $translation->tags()->attach($this->tags->random(2));

        $start = microtime(true);

        $response = $this->deleteJson("/api/translations/{$translation->id}");

        $duration = microtime(true) - $start;

        $response->assertStatus(204);

        fwrite(STDERR, "\nDestroy operation: " . round($duration * 1000, 2) . " ms\n");
        $this->assertLessThan(200, $duration * 1000);
    }

    /** @test */
    public function exportPerformance()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->createUniqueTranslations(200);

        $start = microtime(true);
        $response = $this->getJson('/api/translations/export?' . http_build_query([
                'languages' => [$this->languages->first()->id],
                'tags' => [$this->tags->first()->id]
            ]));
        $duration = microtime(true) - $start;

        $response->assertStatus(200);
        fwrite(STDERR, "\nExport operation: " . round($duration * 1000, 2) . " ms\n");
        $this->assertLessThan(500, $duration * 1000);
    }

    public function cachePerformanceComparison()
    {
        // Create limited dataset for cache test
        Translation::factory()
            ->count(50) // Reduced from 200 to prevent overflow
            ->sequence(fn ($sequence) => ['key' => 'cache.key.'.$sequence->index])
            ->create()
            ->each(function ($translation) {
                $translation->tags()->attach($this->tags->random(2));
            });

        // First request (uncached)
        $start = microtime(true);
        $this->getJson('/api/translations');
        $uncachedDuration = microtime(true) - $start;

        // Second request (cached)
        $start = microtime(true);
        $this->getJson('/api/translations');
        $cachedDuration = microtime(true) - $start;

        fwrite(STDERR, "\nCache performance comparison:");
        fwrite(STDERR, "\nUncached: " . round($uncachedDuration * 1000, 2) . " ms");
        fwrite(STDERR, "\nCached: " . round($cachedDuration * 1000, 2) . " ms\n");

        $this->assertLessThan($uncachedDuration, $cachedDuration, 'Cached response should be faster');
    }
}
