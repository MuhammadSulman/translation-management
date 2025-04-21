<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LanguageControllerFeatureTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // Create and authenticate a user if API routes are protected
        // Uncomment the below code if your routes are protected with auth:sanctum
        /*
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        */
    }

    public function testIndexReturnsAllLanguages()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create test languages
        $languages = Language::factory()->count(3)->create();

        // Call the index endpoint
        $response = $this->getJson('/api/languages');

        // Assert successful response
        $response->assertStatus(200);

        // Assert that the structure matches the expected LanguageResource format
        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'code',
            ],
        ]);
    }

    public function testStoreCreatesNewLanguage()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        // Prepare data for a new language
        $languageData = [
            'name' => 'Italian',
            'code' => 'it'
        ];

        // Call the store endpoint
        $response = $this->postJson('/api/languages', $languageData);

        // Assert successful response
        $response->assertStatus(201);

        // Assert that the response contains the correct data
        $response->assertJson([
            'name' => 'Italian',
            'code' => 'it'
        ]);

        // Assert that the language was actually stored in the database
        $this->assertDatabaseHas('languages', $languageData);
    }

    public function testStoreValidatesRequest()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);
        // Prepare invalid data (missing code)
        $invalidData = [
            'name' => 'Portuguese'
            // code is missing
        ];

        // Call the store endpoint
        $response = $this->postJson('/api/languages', $invalidData);

        // Assert validation error response
        $response->assertStatus(422);

        // Assert that validation errors contain the missing field
        $response->assertJsonValidationErrors(['code']);
    }

    public function testUpdateModifiesExistingLanguage()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create a language to update
        $language = Language::factory()->create([
            'name' => 'Russian',
            'code' => 'ru'
        ]);

        // Prepare update data
        $updateData = [
            'name' => 'Russian (Updated)',
            'code' => 'ru'
        ];

        // Call the update endpoint
        $response = $this->putJson("/api/languages/{$language->id}", $updateData);

        // Assert successful response
        $response->assertStatus(200);

        // Assert that the response contains the updated data
        $response->assertJsonFragment($updateData);

        // Assert that the response has the expected structure
        $response->assertJsonStructure([
            'id',
            'name',
            'code',
        ]);

        // Assert that the language was actually updated in the database
        $this->assertDatabaseHas('languages', array_merge($updateData, ['id' => $language->id]));
    }

    public function testUpdateValidatesRequest()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create a language to update
        $language = Language::factory()->create();

        // Prepare invalid update data (empty name)
        $invalidData = [
            'name' => '',
            'code' => 'jp'
        ];

        // Call the update endpoint
        $response = $this->putJson("/api/languages/{$language->id}", $invalidData);

        // Assert validation error response
        $response->assertStatus(422);

        // Assert that validation errors contain the invalid field
        $response->assertJsonValidationErrors(['name']);
    }

    public function testDestroyRemovesLanguage()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Create a language to delete
        $language = Language::factory()->create();

        // Call the destroy endpoint
        $response = $this->deleteJson("/api/languages/{$language->id}");

        // Assert successful response (no content)
        $response->assertStatus(204);
        $response->assertNoContent();

        // Assert that the language was actually removed from the database
        $this->assertDatabaseMissing('languages', ['id' => $language->id]);
    }

    public function testDestroy404ForNonExistentLanguage()
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Call the destroy endpoint with a non-existent ID
        $response = $this->deleteJson("/api/languages/999");

        // Assert not found response
        $response->assertStatus(404);
    }
}
