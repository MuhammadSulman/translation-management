<?php

namespace Tests\Performance\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerPerformanceTest extends TestCase
{
    use RefreshDatabase;

    protected $baseEndpoint = '/api';
    protected $testUsers = [];
    protected $tokens = [];
    protected $queryCount;

    public function setUp(): void
    {
        parent::setUp();

        // Create test users (adjust number as needed)
        $this->createTestUsers(10);

        // Enable query counting
        DB::enableQueryLog();
    }

    protected function createTestUsers($count): void
    {
        for ($i = 0; $i < $count; $i++) {
            $this->testUsers[] = User::factory()->create([
                'email' => "user{$i}@example.com",
                'password' => Hash::make('password')
            ]);
        }
    }

    protected function getQueryCount(): int
    {
        return count(DB::getQueryLog());
    }

    protected function resetQueryLog(): void
    {
        DB::flushQueryLog();
    }

    public function testLoginQueryPerformance(): void
    {
        // Reset query log
        $this->resetQueryLog();

        // Perform login action
        $response = $this->postJson("{$this->baseEndpoint}/login", [
            'email' => $this->testUsers[0]->email,
            'password' => 'password'
        ]);

        // Get query count
        $queryCount = $this->getQueryCount();

        // Assert successful response
        $response->assertStatus(200);
        $response->assertJsonStructure(['token']);

        // Maximum allowed queries for login
        $maxQueries = 5;

        // Assert query count is below threshold
        $this->assertLessThanOrEqual(
            $maxQueries,
            $queryCount,
            "Login is using {$queryCount} queries, which exceeds the maximum of {$maxQueries}"
        );
    }

    public function testLoginResponseTime()
    {
        // Measure response time
        $startTime = microtime(true);

        $response = $this->postJson("{$this->baseEndpoint}/login", [
            'email' => $this->testUsers[0]->email,
            'password' => 'password'
        ]);

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Assert successful response
        $response->assertStatus(200);

        // Maximum allowed response time (in milliseconds)
        $maxResponseTime = 500;

        // Assert response time is below threshold
        $this->assertLessThanOrEqual(
            $maxResponseTime,
            $executionTime,
            "Login took {$executionTime}ms, which exceeds the maximum of {$maxResponseTime}ms"
        );
    }

    public function testLogoutQueryPerformance()
    {
        // Create authenticated session
        $user = $this->testUsers[0];
        Sanctum::actingAs($user);

        // Reset query log
        $this->resetQueryLog();

        // Perform logout
        $response = $this->postJson("{$this->baseEndpoint}/logout");

        // Get query count
        $queryCount = $this->getQueryCount();

        // Assert successful response
        $response->assertStatus(200);

        // Maximum allowed queries for logout
        $maxQueries = 3;

        // Assert query count is below threshold
        $this->assertLessThanOrEqual(
            $maxQueries,
            $queryCount,
            "Logout is using {$queryCount} queries, which exceeds the maximum of {$maxQueries}"
        );
    }

    public function testLogoutResponseTime()
    {
        // Create authenticated session
        $user = $this->testUsers[0];
        Sanctum::actingAs($user);

        // Measure response time
        $startTime = microtime(true);

        $response = $this->postJson("{$this->baseEndpoint}/logout");

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Assert successful response
        $response->assertStatus(200);

        // Maximum allowed response time (in milliseconds)
        $maxResponseTime = 200;

        // Assert response time is below threshold
        $this->assertLessThanOrEqual(
            $maxResponseTime,
            $executionTime,
            "Logout took {$executionTime}ms, which exceeds the maximum of {$maxResponseTime}ms"
        );
    }

    public function testLoginUnderLoad()
    {
        $sampleSize = 5;
        $totalTime = 0;
        $maxTime = 0;

        // Perform multiple login requests and measure performance
        for ($i = 0; $i < $sampleSize; $i++) {
            $userIndex = $i % count($this->testUsers);

            $startTime = microtime(true);

            $response = $this->postJson("{$this->baseEndpoint}/login", [
                'email' => $this->testUsers[$userIndex]->email,
                'password' => 'password'
            ]);

            $executionTime = (microtime(true) - $startTime) * 1000;
            $totalTime += $executionTime;
            $maxTime = max($maxTime, $executionTime);

            // Store token for potential further tests
            if ($response->status() === 200) {
                $this->tokens[] = $response->json('token');
            }

            // Add a small delay to prevent rate limiting
            usleep(100000); // 100ms
        }

        $averageTime = $totalTime / $sampleSize;

        // Maximum allowed average response time (in milliseconds)
        $maxAverageTime = 300;

        // Assert average response time is below threshold
        $this->assertLessThanOrEqual(
            $maxAverageTime,
            $averageTime,
            "Average login time under load was {$averageTime}ms, which exceeds the maximum of {$maxAverageTime}ms"
        );
    }

    public function testLoginWithConcurrentUsers()
    {
        // This simulates a basic load test
        // Note: In real scenarios, you'd use dedicated load testing tools like k6, JMeter, or Laravel Dusk

        $concurrentRequests = 3;
        $startTimes = [];
        $endTimes = [];

        // Start multiple processes
        for ($i = 0; $i < $concurrentRequests; $i++) {
            $userIndex = $i % count($this->testUsers);
            $startTimes[$i] = microtime(true);

            // In a real test, you'd use something like parallel processes or curl_multi
            // For this demo, we're making sequential requests
            $response = $this->postJson("{$this->baseEndpoint}/login", [
                'email' => $this->testUsers[$userIndex]->email,
                'password' => 'password'
            ]);

            $endTimes[$i] = microtime(true);

            // All should be successful
            $this->assertEquals(200, $response->status());
        }

        // Calculate metrics
        $totalTime = 0;
        for ($i = 0; $i < $concurrentRequests; $i++) {
            $executionTime = ($endTimes[$i] - $startTimes[$i]) * 1000;
            $totalTime += $executionTime;
        }

        $averageTime = $totalTime / $concurrentRequests;

        // Maximum allowed average response time under concurrent load (in milliseconds)
        $maxConcurrentTime = 400;

        // Assert average response time is below threshold
        $this->assertLessThanOrEqual(
            $maxConcurrentTime,
            $averageTime,
            "Average login time under concurrent load was {$averageTime}ms, which exceeds the maximum of {$maxConcurrentTime}ms"
        );
    }

    public function tearDown(): void
    {
        // Clean up
        DB::disableQueryLog();
        parent::tearDown();
    }
}
