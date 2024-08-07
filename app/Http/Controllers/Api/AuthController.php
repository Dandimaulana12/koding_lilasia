<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function register(Request $request)
    {
        try {
            Log::info('-------Register---------');
            $response = $this->authService->register($request->all());
            Log::info(['from' => 'register', $response]);
            return response()->json($response, 201);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $formattedErrors = [];
            Log::info(['from' => 'register', 'error validation:' => $errors]);
            foreach ($errors as $field => $messages) {
                $formattedErrors[] = ucfirst($field) . ': ' . implode(', ', $messages);
            }
            Log::info([
                'from' => 'register',
                'status' => false,
                'message' => 'Validation failed: ' . implode(' | ', $formattedErrors),
                'errors' => $e->errors(),
            ]);

            return response()->json([
                'status' => false,
                'message' => 'Validation failed: ' . implode(' | ', $formattedErrors),
                'errors' => $e->errors(),
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $e) {
            Log::info([
                'from' => 'register',
                'status' => false,
                'message' => 'An unexpected error occurred. Please try again later.',
                'errors' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => false,
                'message' => 'An unexpected error occurred. Please try again later.',
                'errors' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function login(Request $request)
    {
        try {
            $response = $this->authService->login($request->only('email', 'password'));
            return response()->json($response);
        } catch (BadRequestException $e) {
            Log::info([
                'from' => 'login',
                'status' => false,
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            Log::info([
                'from' => 'login',
                'status' => false,
                'message' => 'An unexpected error occurred. Please try again later.',
                'errors' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => false,
                'message' => 'An unexpected error occurred. Please try again later.',
                'errors' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function me(Request $request)
    {
        return response()->json([
            'status' => true,
            'message' => 'User information retrieved successfully',
            'data' => $request->user(),
        ]);
    }
}
