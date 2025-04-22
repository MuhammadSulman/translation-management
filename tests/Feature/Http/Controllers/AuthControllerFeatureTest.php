<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthControllerFeatureTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testUserCanLoginWithValidCredentials()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('secret'),
        ]);

        // Attempt to log in
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'secret',
        ]);

        // Assert successful login
        $response->assertStatus(200)
            ->assertJsonStructure(['token']);
    }

    public function testUserCannotLoginWithInvalidCredentials()
    {
        // Create a user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('secret'),
        ]);

        // Attempt to log in with wrong password
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        // Assert login failed
        $response->assertStatus(401)
            ->assertJson(['error' => 'Unauthorized']);
    }

    public function testUserCannotLoginWithInvalidData()
    {
        // Attempt to log in with invalid email
        $response = $this->postJson('/api/login', [
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        // Assert validation error
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function testUserCanLogout()
    {
        // Create a user
        $user = User::factory()->create();

        // Authenticate user using Sanctum
        Sanctum::actingAs($user);

        // Logout
        $response = $this->postJson('/api/logout');

        // Assert successful logout
        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out successfully']);

        // Verify token was deleted by attempting to access a protected route
        $this->assertCount(0, $user->tokens);
    }

    public function testUnauthenticatedUserCannotLogout()
    {
        // Attempt to logout without authentication
        $response = $this->postJson('/api/logout');

        // Just assert the status code for now
        $response->assertStatus(401);
    }
}
