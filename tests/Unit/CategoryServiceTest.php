<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Config;
use Illuminate\Validation\ValidationException;

class CategoryServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CategoryService $categoryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->categoryService = new CategoryService();
    }

    public function testGetAllCategories()
    {
        Category::create(['name' => 'Category 1']);
        Category::create(['name' => 'Category 2']);
        Category::create(['name' => 'Category 3']);

        $response = $this->categoryService->getAllCategories();

        $this->assertTrue($response['status']);
        $this->assertEquals('Success get all category', $response['message']);
        $this->assertIsArray($response['data']);
        $this->assertCount(3, $response['data']);
    }

    public function testGetCategoryById()
    {
        $category = Category::create(['name' => 'Category 1']);

        $response = $this->categoryService->getCategoryById($category->id);

        $this->assertTrue($response['status']);
        $this->assertEquals('Success get single category', $response['message']);
        $this->assertEquals($category->id, $response['data']['id']);
        $this->assertEquals($category->name, $response['data']['name']);
    }

    public function testGetCategoryByIdNotFound()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->categoryService->getCategoryById(999);
    }

    public function testCreateCategory()
    {
        $data = ['name' => 'New Category'];

        $response = $this->categoryService->createCategory($data);

        $this->assertTrue($response['status']);
        $this->assertEquals('Category Created Successfully', $response['message']);
        $this->assertEquals('New Category', $response['data']->name);
    }

    public function testCreateCategoryValidationException()
    {
        $data = [];

        $this->expectException(ValidationException::class);
        $this->categoryService->createCategory($data);
    }

    public function testUpdateCategory()
    {
        $category = Category::create(['name' => 'Old Name']);
        $data = ['name' => 'Updated Name'];

        $response = $this->categoryService->updateCategory($category->id, $data);

        $this->assertTrue($response['status']);
        $this->assertEquals('Category Updated Successfully', $response['message']);
        $this->assertEquals('Updated Name', $response['data']->name);
    }

    public function testUpdateCategoryValidationException()
    {
        $existingCategory = Category::create(['name' => 'Existing Name']);
    
        $category = Category::create(['name' => 'Unique Name']);
    
        $data = ['name' => 'Existing Name'];
    
        $this->expectException(ValidationException::class);
        $this->categoryService->updateCategory($category->id, $data);
    }

    public function testDeleteCategory()
    {
        $category = Category::create(['name' => 'Category to Delete']);

        $response = $this->categoryService->deleteCategory($category->id);

        $this->assertTrue($response['status']);
        $this->assertEquals('Category Deleted Successdully', $response['message']);

        $this->assertNull(Category::find($category->id));
    }
    
    public function testDeleteCategoryNotFound()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->categoryService->deleteCategory(999);
    }
}
