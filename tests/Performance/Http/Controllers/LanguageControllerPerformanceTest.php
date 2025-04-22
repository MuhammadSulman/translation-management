<?php

namespace Tests\Performance\Http\Controllers;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LanguageControllerPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable middleware if needed for pure performance testing
        // $this->withoutMiddleware();
    }

    /** @test */
    public function indexPerformanceWithSmallDataset()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        // Seed a small dataset (10 records)
        Language::factory()->count(10)->create();

        // Measure performance
        $start = microtime(true);

        $response = $this->getJson('/api/languages');

        $end = microtime(true);
        $duration = $end - $start;

        $response->assertStatus(200);

        // Output the duration
        fwrite(STDERR, "\nIndex with small dataset (10 records): " . round($duration * 1000, 2) . " ms\n");

        // Assert that the response time is acceptable (adjust threshold as needed)
        $this->assertLessThan(100, $duration * 1000, 'Response time should be less than 100ms');
    }

    /** @test */
    public function indexPerformanceWithLargeDataset()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        // Disable mass assignment protection if needed
        Language::unguard();

        // Seed a large dataset (1000 records) without using factories for unique fields
        $languages = [];
        for ($i = 0; $i < 1000; $i++) {
            $languages[] = [
                'name' => 'Language ' . $i,
                'code' => 'l' . $i,  // Ensure code is unique
                // Add other required fields
            ];
        }

        // Insert all at once for better performance
        Language::insert($languages);

        // Re-enable mass assignment protection
        Language::reguard();

        // Measure performance
        $start = microtime(true);

        $response = $this->getJson('/api/languages');

        $end = microtime(true);
        $duration = $end - $start;

        $response->assertStatus(200);

        // Output the duration
        fwrite(STDERR, "\nIndex with large dataset (1000 records): " . round($duration * 1000, 2) . " ms\n");

        // Assert that the response time is acceptable (adjust threshold as needed)
        $this->assertLessThan(500, $duration * 1000, 'Response time should be less than 500ms');
    }

    /** @test */
    public function storePerformance()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $data = [
            'name' => 'Test Language',
            'code' => 'tl',
            'is_active' => true,
        ];

        // Measure performance
        $start = microtime(true);

        $response = $this->postJson('/api/languages', $data);

        $end = microtime(true);
        $duration = $end - $start;

        $response->assertStatus(201);

        // Output the duration
        fwrite(STDERR, "\nStore operation: " . round($duration * 1000, 2) . " ms\n");

        // Assert that the response time is acceptable (adjust threshold as needed)
        $this->assertLessThan(200, $duration * 1000, 'Response time should be less than 200ms');
    }

    /** @test */
    public function updatePerformance()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $language = Language::factory()->create();
        $data = [
            'name' => 'Updated Language',
            'code' => 'ul',
            'is_active' => false,
        ];

        // Measure performance
        $start = microtime(true);

        $response = $this->putJson("/api/languages/{$language->id}", $data);

        $end = microtime(true);
        $duration = $end - $start;

        $response->assertStatus(200);

        // Output the duration
        fwrite(STDERR, "\nUpdate operation: " . round($duration * 1000, 2) . " ms\n");

        // Assert that the response time is acceptable (adjust threshold as needed)
        $this->assertLessThan(200, $duration * 1000, 'Response time should be less than 200ms');
    }

    /** @test */
    public function destroyPerformance()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $language = Language::factory()->create();

        // Measure performance
        $start = microtime(true);

        $response = $this->deleteJson("/api/languages/{$language->id}");

        $end = microtime(true);
        $duration = $end - $start;

        $response->assertStatus(204);

        // Output the duration
        fwrite(STDERR, "\nDestroy operation: " . round($duration * 1000, 2) . " ms\n");

        // Assert that the response time is acceptable (adjust threshold as needed)
        $this->assertLessThan(200, $duration * 1000, 'Response time should be less than 200ms');
    }

    /** @test */
    public function concurrentRequestsPerformance()
    {
        Language::factory()->count(100)->create();

        $concurrentRequests = 10;
        $totalDuration = 0;
        $startAll = microtime(true);

        // Simulate concurrent requests
        for ($i = 0; $i < $concurrentRequests; $i++) {
            $start = microtime(true);
            $this->getJson('/api/languages');
            $end = microtime(true);
            $totalDuration += ($end - $start);
        }

        $endAll = microtime(true);
        $overallDuration = $endAll - $startAll;
        $averageDuration = $totalDuration / $concurrentRequests;

        // Output the durations
        fwrite(STDERR, "\nConcurrent requests (10):");
        fwrite(STDERR, "\nAverage per request: " . round($averageDuration * 1000, 2) . " ms");
        fwrite(STDERR, "\nTotal time: " . round($overallDuration * 1000, 2) . " ms\n");

        // Assert that the average response time is acceptable
        $this->assertLessThan(300, $averageDuration * 1000, 'Average response time should be less than 300ms');
    }
}
