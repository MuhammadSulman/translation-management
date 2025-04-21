<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Language;
use App\Models\Tag;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TranslationControllerFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function testCanListTranslations(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Translation::factory()->count(5)->create();

        $response = $this->getJson('/api/translations');

        $response->assertOk()
            ->assertJsonCount(5, 'data');
    }

    public function testCanCreateTranslationWithTags(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $language = Language::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $response = $this->postJson('/api/translations', [
            'key' => 'greeting.hello',
            'value' => 'Hello',
            'language_id' => $language->id,
            'tags' => $tags->pluck('id')->toArray(),
        ]);

        $response->assertCreated()
            ->assertJsonPath('key', 'greeting.hello');

        $this->assertDatabaseHas('translations', ['key' => 'greeting.hello']);
        $this->assertCount(2, Translation::first()->tags);
    }

    public function testCanUpdateTranslation(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $language = Language::factory()->create();
        $tags = Tag::factory()->count(2)->create();

        $translation = Translation::factory()->create([
            'key' => 'welcome.message',
            'value' => 'Welcome',
            'language_id' => $language->id,
        ]);

        $response = $this->putJson('/api/translations/' . $translation->id, [
            'key' => 'welcome.message',
            'value' => 'Welcome back',
            'language_id' => $language->id,
            'tags' => $tags->pluck('id')->toArray(),
        ]);

        $response->assertOk()
            ->assertJsonPath('value', 'Welcome back');
    }

    public function testCanDeleteTranslation(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $translation = Translation::factory()->create();

        $response = $this->deleteJson('/api/translations/' . $translation->id);

        $response->assertNoContent();
    }

    public function testCanExportTranslationsAsJson(): void
    {
        // Create a user and authenticate
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        Translation::factory()->create();

        $response = $this->get('/api/translations/export');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/json')
            ->assertHeader('Content-Disposition');
    }
}
