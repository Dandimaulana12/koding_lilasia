<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CategoryService
{

    public function getAllCategories(): array
    {
        $category = Category::all()->toArray();

        return [
            'status'=> true,
            'message'=> 'Success get all category',
            'data'=> $category
        ];
    }

    public function getCategoryById(int $id): array
    {
        $category = Category::find($id);

        if (!$category) {
            throw new ModelNotFoundException('Category Not Found');
        }

        return [
            'status'=> true,
            'message'=> 'Success get single category',
            'data'=> $category->toArray()
        ];
    }

    public function createCategory(array $data): array
    {

        $validator = Validator::make($data, [
            'name' => 'required|string|max:255|unique:categories'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $category = Category::create($data);

        return [
            'status' => true,
            'message' => 'Category Created Successfully',
            'data' => $category
        ];
    }


    public function updateCategory(int $id, array $data): array
    {
        $category = Category::find($id);

        if (!$category) {
            throw new ModelNotFoundException('Category Not Found');
        }

        $validator = Validator::make($data, [
            'name' => 'sometimes|required|string|max:255|unique:categories,name,' . $id
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $category->update($data);

        return [
            'status' => true,
            'message' => 'Category Updated Successfully',
            'data' => $category
        ];
    }

    public function deleteCategory(int $id): array
    {
        $category = Category::find($id);

        if (!$category) {
            throw new ModelNotFoundException('Category Not Found');
        }

        $category->delete();

        return [
            'status' => true,
            'message' => 'Category Deleted Successdully',

        ];
    }
}
