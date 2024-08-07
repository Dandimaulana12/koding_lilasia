<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CategoryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index()
    {
        try {
            Log::info('-----------category index--------');

            $response = $this->categoryService->getAllCategories();
            Log::info($response);

            return response()->json($response, Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::info([
                'from' => 'category index',
                'status' => false,
                'message' => 'An unexpected error occured'
            ]);
            return response()->json([
                'status' => false,
                'message' => 'An unexpected error occured'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        try {
            Log::info('-----------category show--------');
            if (!ctype_digit($id)) {
                throw new InvalidArgumentException('ID must be an integer');
            }

            $response = $this->categoryService->getCategoryById($id);

            return response()->json($response, Response::HTTP_OK);
            Log::info($response);
        } catch (ModelNotFoundException $e) {
            Log::info([
                'from' => 'category show',
                'status' => false,
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::info([
                'from' => 'category show',
                'status' => false,
                'message' => 'An unexpected error occured'
            ]);
            return response()->json([
                'status' => false,
                'message' => 'An unexpected error occured:'.$e->getMessage(),
                'errors'=> $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function store(Request $request)
    {
        try {
            Log::info('-----------category store--------');
            $response = $this->categoryService->createCategory($request->all());
            Log::info($response);
            return response()->json($response);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $formattedErrors = [];
            Log::info(['from' => 'product update', 'error validation:'=> $errors]);
            foreach ($errors as $field => $messages) {
                $formattedErrors[] = ucfirst($field) . ': ' . implode(', ', $messages);
            }

            Log::info([
                'from' => 'category store',
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
                'from' => 'category store',
                'status' => false,
                'message' => 'An unexpected error occurred.',
                'errors' => $e->getMessage(), // Include the exception message for debugging
            ]);
            return response()->json([
                'status' => false,
                'message' => 'An unexpected error occurred.',
                'errors' => $e->getMessage(), // Include the exception message for debugging
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            Log::info('-----------category update--------');
            
            if (!ctype_digit($id)) {
                throw new InvalidArgumentException('ID must be an integer');
            }

            $response = $this->categoryService->updateCategory($id, $request->all());
            Log::info($response);
            return response()->json($response, Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            Log::info([
                'from' => 'category update',
                'status' => false,
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        }  catch (ValidationException $e) {
            $errors = $e->errors();
            $formattedErrors = [];
        
            Log::info(['from' => 'product update', 'error validation:'=> $errors]);
            foreach ($errors as $field => $messages) {
                $formattedErrors[] = ucfirst($field) . ': ' . implode(', ', $messages);
            }
        
            Log::info([
                'from' => 'category update',
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
                'from' => 'category update',
                'status' => false,
                'message' => 'An unexpected error occurred.',
                'errors' => $e->getMessage(), // Include the exception message for debugging
            ]);
            return response()->json([
                'status' => false,
                'message' => 'An unexpected error occurred:'. $e->getMessage(),
                'errors' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            Log::info('-----------category delete--------');
            if (!ctype_digit($id)) {
                throw new InvalidArgumentException('ID must be an integer');
            }

            $response = $this->categoryService->deleteCategory($id);
            Log::info($response);
            return response()->json($response);
        } catch (ModelNotFoundException $e) {
            Log::info([
                'from' => 'category delete',
                'status' => false,
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::info([
                'from' => 'category delete',
                'status' => false,
                'message' => 'An unexpected error occurred.',
                'errors' => $e->getMessage(), // Include the exception message for debugging
            ]);
            return response()->json([
                'status' => false,
                'message' => 'An unexpected error occurred:'.$e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
