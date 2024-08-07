<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class ProductController extends Controller
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(Request $request)
    {
        try {
            Log::info('-----------product index--------');
            // Pass filters from query parameters if they exist
            $filters = $request->only(['category', 'price_min', 'price_max', 'name']);
            $response = $this->productService->getAllProducts($filters);

            Log::info($response);
            return response()->json($response, Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::info([
                'from' => 'product index',
                'status' => false,
                'message' => 'An unexpected error occured'
            ]);
            return response()->json([
                'status' => false,
                'message' => 'An unexpected error occurred'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function show($id)
    {
        try {
            Log::info('-----------product show--------');
            if (!ctype_digit($id)) {
                throw new InvalidArgumentException('ID must be an integer');
            }

            $response = $this->productService->getProductById($id);

            Log::info($response);
            return response()->json($response, Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            Log::info([
                'from' => 'product show',
                'status' => false,
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::info([
                'from' => 'product show',
                'status' => false,
                'message' => 'An unexpected error occured'
            ]);
            return response()->json([
                'status' => false,
                'message' => 'An unexpected error occured:'. $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    public function store(Request $request)
    {
        try {
            Log::info('-----------product store--------');
            
            $response = $this->productService->createProduct($request->all());

            return response()->json($response, Response::HTTP_CREATED);
            Log::info($response);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $formattedErrors = [];

            Log::info(['from' => 'product update', 'error validation:'=> $errors]);
            foreach ($errors as $field => $messages) {
                $formattedErrors[] = ucfirst($field) . ': ' . implode(', ', $messages);
            }

            Log::info([
                'from' => 'product store',
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
                'from' => 'product store',
                'status' => false,
                'message' => 'An unexpected error occurred.',
                'errors' => $e->getMessage(), // Include the exception message for debugging
            ]);
            return response()->json([
                'status' => false,
                'message' => 'An unexpected error occurred:'. $e->getMessage(),
                'errors' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            Log::info('-------product update---------');

            if (!ctype_digit($id)) {
                throw new InvalidArgumentException('ID must be an integer');
            }
            $product = $this->productService->updateProduct($id, $request->all());

            Log::info($product);
            return response()->json($product, Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            Log::info([
                'from' => 'product update',
                'status' => false,
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (ValidationException $e) {
            $errors = $e->errors();
            $formattedErrors = [];

            Log::info(['from' => 'product update', 'error validation:'=> $errors]);
            foreach ($errors as $field => $messages) {
                $formattedErrors[] = ucfirst($field) . ': ' . implode(', ', $messages);
            }

            Log::info([
                'from' => 'product update',
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
                'from' => 'product update',
                'status' => false,
                'message' => 'An unexpected error occurred.',
                'errors' => $e->getMessage(), // Include the exception message for debugging
            ]);

            return response()->json([
                'status' => false,
                'message' => 'An unexpected error occurred:'. $e->getMessage(),
                'errors' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function destroy($id)
    {
        try {
            Log::info('-------product delete---------');
            if (!ctype_digit($id)) {
                throw new InvalidArgumentException('ID must be an integer');
            }
            $response = $this->productService->deleteProduct($id);

            Log::info($response);
            return response()->json($response, Response::HTTP_OK);
        } catch (ModelNotFoundException $e) {
            Log::info([
                'from' => 'product delete',
                'status' => false,
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            Log::info([
                'from' => 'product delete',
                'status' => false,
                'message' => 'An unexpected error occurred.',
                'errors' => $e->getMessage(), // Include the exception message for debugging
            ]);
            return response()->json([
                'status' => false,
                'message' => 'An unexpected error occurred:'. $e->getMessage(),
                'errors' => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
