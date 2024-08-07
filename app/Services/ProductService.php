<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ProductService
{

    public function getAllProducts(array $filters = []): array
    {
        $query = Product::query();

        // Apply category filter if provided
        if (isset($filters['category'])) {
            $query->where('id_category', $filters['category']); // Assuming category_id is the foreign key
        }

        // Apply price range filter if provided
        if (isset($filters['price_min']) && isset($filters['price_max'])) {
            $query->whereBetween('price', [$filters['price_min'], $filters['price_max']]);
        }

        // Apply search by name filter if provided
        if (isset($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        // Apply default sorting if no filters are provided
        if (empty($filters)) {
            $query->orderBy('created_at', 'asc'); // Sort by price in ascending order
        }

        $products = $query->get()->toArray();

        return [
            'status' => true,
            'message' => 'Success get all products',
            'data' => $products
        ];
    }

    public function getProductById(int $id): array
    {
        $product = Product::find($id);

        if (!$product) {
            throw new ModelNotFoundException('Product Not Found');
        }

        return [
            'status' => true,
            'message' => 'Successfully Get Single Product',
            'data' => $product->toArray()
        ];
    }

    public function createProduct(array $data): array
    {
        Log::info('-------product create service---------');

        Log::info(['from:'=>'product update service', 'data'=> $data]);

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'id_category' => 'required|exists:categories,id',
            'description' => 'required|string|string',
            'price' => 'required|numeric',
            'image' => 'required|image|max:2048',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        if (isset($data['image'])) {
            $imagePath = $data['image']->store('products', 'public');
            $data['image'] = $imagePath;
        }

        $product =  Product::create($data);
        return [
            'status' => true,
            'message' => 'Product Created Successfully',
            'data' => $product
        ];
    }

    public function updateProduct($id, array $data): array
    {
        Log::info('-------product update service---------');
        $product = Product::find($id);

        if (!$product) {
            throw new ModelNotFoundException('Product Not Found');
        }

        Log::info(['from:'=>'product update service', 'data'=> $data]);

        $validator = Validator::make($data, [
            'name' => 'sometimes|string|max:255',
            'id_category' => 'sometimes|required|exists:categories,id',
            'description' => 'sometimes|string',
            'price' => 'sometimes|required|numeric',
            'image' => 'sometimes|nullable|image|max:2048',
        ]);

        if (array_key_exists('name', $data) && $data['name'] === null) {
            throw new ValidationException($validator, 'The name field cannot be null if present.');
        }

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        if (isset($data['image'])) {
            // Delete the old image
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $imagePath = $data['image']->store('products', 'public');
            $data['image'] = $imagePath;
        }

        $product->update($data);

        return [
            'status' => true,
            'message' => 'Product Updated Successfully',
            'data' => $product
        ];
    }

    public function deleteProduct($id)
    {
        $product = Product::find($id);

        if (!$product) {
            throw new ModelNotFoundException('Product Not Found');
        }

        // Delete the image
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return [
            'status' => true,
            'message' => 'Product Deleted Successfully',

        ];
    }
}
