<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testRegisterSuccess()
    {
        $request = Request::create('/register', 'POST', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'user'
        ]);

        $response = $this->postJson('/api/register', $request->all());

        $response->assertStatus(201);
        $response->assertJson(['status' => true, 'message' => 'User registered successfully']);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function testRegisterValidationFails()
    {
        $requestData = [];

        $response = $this->postJson('/api/register', $requestData);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $response->assertJson([
            'status' => false,
            'message' => 'Validation failed: Name: The name field is required. | Email: The email field is required. | Password: The password field is required. | Role: The role field is required.',
            'errors' => [
                'name' => ['The name field is required.'],
                'email' => ['The email field is required.'],
                'password' => ['The password field is required.'],
                'role' => ['The role field is required.'],
            ],
        ]);
    }

    public function testLoginSuccess()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'), // Hash the password
        ]);

        $requestData = [
            'email' => 'test@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $requestData);

        $response->assertStatus(Response::HTTP_OK);

        $response->assertJsonStructure([
            'status',
            'message',
            'data' => ['token'],
        ]);

        $response->assertJson([
            'status' => true,
            'message' => 'Login successful',
        ]);
    }

    public function testLoginFailsWithInvalidEmail()
    {
        $requestData = [
            'email' => 'nonexistent@example.com',
            'password' => 'password123',
        ];

        $response = $this->postJson('/api/login', $requestData);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);

        $response->assertJson([
            'status' => false,
            'message' => 'Email not found',
        ]);
    }

    public function testLoginFailsWithIncorrectPassword()
    {

        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'), // Hash the password
        ]);

        $requestData = [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ];

        $response = $this->postJson('/api/login', $requestData);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);

        $response->assertJson([
            'status' => false,
            'message' => 'Incorrect password',
        ]);
    }
}
