<?php

namespace Tests\Unit;

namespace Tests\Unit\Http\Controllers;

use App\Http\Controllers\API\AuthController;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\NewAccessToken;
use Mockery;
use Tests\TestCase;

class AuthControllerUnitTest extends TestCase
{
    use RefreshDatabase;

    protected $authController;

    public function setUp(): void
    {
        parent::setUp();
        $this->authController = new AuthController();
    }

    public function testLoginSuccessful()
    {
        // Create a mock for LoginRequest
        $request = Mockery::mock(LoginRequest::class);
        $request->shouldReceive('validated')
            ->once()
            ->andReturn(['email' => 'test@example.com', 'password' => 'password']);

        // Create a mock for User
        $user = Mockery::mock(User::class);

        // Create a mock for token
        $accessToken = Mockery::mock(NewAccessToken::class);
        $accessToken->plainTextToken = 'test-token-string';

        $user->shouldReceive('createToken')
            ->once()
            ->with('auth-token')
            ->andReturn($accessToken);

        // Set up Auth facade to return our mocked user
        Auth::shouldReceive('attempt')
            ->once()
            ->with(['email' => 'test@example.com', 'password' => 'password'])
            ->andReturn(true);

        Auth::shouldReceive('user')
            ->once()
            ->andReturn($user);

        $response = $this->authController->login($request);

        // Assert that the response is correct
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['token' => 'test-token-string'], json_decode($response->getContent(), true));
    }

    public function testLoginFailed()
    {
        // Create a mock for LoginRequest
        $request = Mockery::mock(LoginRequest::class);
        $request->shouldReceive('validated')
            ->once()
            ->andReturn(['email' => 'test@example.com', 'password' => 'wrong-password']);

        // Set up Auth facade to return false
        Auth::shouldReceive('attempt')
            ->once()
            ->with(['email' => 'test@example.com', 'password' => 'wrong-password'])
            ->andReturn(false);

        $response = $this->authController->login($request);

        // Assert that the response is correct
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals(['error' => 'Unauthorized'], json_decode($response->getContent(), true));
    }

    public function testLogoutSuccessful()
    {
        // Create a mock for Request and token
        $request = Mockery::mock(Request::class);
        $token = Mockery::mock();

        // Set up token behavior
        $token->shouldReceive('delete')
            ->once()
            ->andReturn(true);

        // Set up user behavior
        $user = Mockery::mock(User::class);
        $user->shouldReceive('currentAccessToken')
            ->once()
            ->andReturn($token);

        // Set up request behavior
        $request->shouldReceive('user')
            ->once()
            ->andReturn($user);


        // Set up Auth facade with a proper guard mock
        $guard = Mockery::mock('guard');
        $guard->shouldReceive('check')
            ->once()
            ->andReturn(true);

        Auth::shouldReceive('guard')
            ->once()
            ->with('sanctum')
            ->andReturn($guard);

        $response = $this->authController->logout($request);

        // Assert that the response is correct
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals(['message' => 'Logged out successfully'], json_decode($response->getContent(), true));
    }

    public function testLogoutUnauthenticated()
    {
        // Create a mock for Request
        $request = Mockery::mock(Request::class);

        $guard = Mockery::mock('guard');

        $guard->shouldReceive('check')
            ->once()
            ->andReturn(false);
        // Set up Auth facade
        Auth::shouldReceive('guard')
            ->once()
            ->with('sanctum')
            ->andReturn($guard);

        $response = $this->authController->logout($request);

        // Assert that the response is correct
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals(['message' => 'Unauthenticated'], json_decode($response->getContent(), true));
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
