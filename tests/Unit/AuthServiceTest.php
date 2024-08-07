<?php

namespace Tests\Unit;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class AuthServiceTest extends TestCase
{

    use RefreshDatabase;

    protected AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService();
    }

    public function test_register_user_successfully()
    {
        $data = [
            'name' => 'dandi',
            'email' => 'dandi@gmail.com',
            'password' => 'dandi123',
            'password_confirmation' => 'dandi123',
            'role' => 'user',
        ];

        $response = $this->authService->register($data);

        $this->assertTrue($response['status']);
        $this->assertEquals('User registered successfully', $response['message']);
        $this->assertDatabaseHas('users', ['email' => 'dandi@gmail.com']);
    }

    public function test_register_user_validation_failure()
    {
        $this->expectException(ValidationException::class);

        $data = [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'does-not-match',
            'role' => 'invalid-role',
        ];

        $this->authService->register($data);
    }

    public function test_login_user_successfully()
    {
        $user = User::factory()->create([
            'email' => 'dandi@gmail.com',
            'password' => Hash::make('dandi123'),
        ]);

        $response = $this->authService->login([
            'email' => 'dandi@gmail.com',
            'password' => 'dandi123',
        ]);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('token', $response['data']);
        $this->assertNotEmpty($response['data']['token']);
    }

    public function test_login_user_invalid_credentials()
    {
        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Incorrect password');
    
        $user = User::factory()->create([
            'email' => 'dandi@gmail.com',
            'password' => Hash::make('dandi123'),
        ]);
    
        $this->authService->login([
            'email' => 'dandi@gmail.com',
            'password' => 'wrongpassword',
        ]);
    }
}
