<?php

namespace Tests\Unit;

use App\Models\Category;
use Illuminate\Foundation\Testing\TestCase;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ProductService $productService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->productService = new ProductService();
    }

    public function testGetAllProductsReturnsAllProductsWhenNoFiltersAreProvided()
    {
        Product::factory()->count(5)->create();

        $response = $this->productService->getAllProducts();

        $this->assertTrue($response['status']);
        $this->assertEquals('Success get all products', $response['message']);
        $this->assertCount(5, $response['data']);
    }

    public function testGetAllProductsFiltersByCategory()
    {
        $category1 = Category::create(['name' => 'Category 1']);
        $category2 = Category::create(['name' => 'Category 2']);

        Product::factory()->count(3)->create(['id_category' => $category1->id]);
        Product::factory()->count(2)->create(['id_category' => $category2->id]);

        $filters = ['category' => $category1->id];
        $response = $this->productService->getAllProducts($filters);

        $this->assertTrue($response['status']);
        $this->assertEquals('Success get all products', $response['message']);
        $this->assertCount(3, $response['data']);
    }

    public function testGetAllProductsFiltersByPriceRange()
    {
        Product::factory()->create(['price' => 50]);
        Product::factory()->create(['price' => 150]);
        Product::factory()->create(['price' => 200]);

        $filters = ['price_min' => 100, 'price_max' => 200];

        $response = $this->productService->getAllProducts($filters);

        $this->assertTrue($response['status']);
        $this->assertEquals('Success get all products', $response['message']);
        $this->assertCount(2, $response['data']);
    }

    public function testGetAllProductsFiltersByName()
    {
        // Arrange: Create products with different names
        Product::factory()->create(['name' => 'Product A']);
        Product::factory()->create(['name' => 'Product B']);
        Product::factory()->create(['name' => 'Another Product']);

        $filters = ['name' => 'Product'];

        // Act: Call the method with the name filter
        $response = $this->productService->getAllProducts($filters);

        // Assert: Check the response
        $this->assertTrue($response['status']);
        $this->assertEquals('Success get all products', $response['message']);
        $this->assertCount(3, $response['data']); // Products with names containing 'Product'
    }

    public function testGetAllProductsDefaultSorting()
    {
        $twoDaysAgo = now()->subDays(2)->format('Y-m-d\TH:i:s.000000\Z');
        $oneDayAgo = now()->subDays(1)->format('Y-m-d\TH:i:s.000000\Z');
        $today = now()->format('Y-m-d\TH:i:s.000000\Z');

        Product::factory()->create(['created_at' => now()->subDays(2)]);
        Product::factory()->create(['created_at' => now()->subDays(1)]);
        Product::factory()->create(['created_at' => now()]);

        $response = $this->productService->getAllProducts();

        $this->assertTrue($response['status']);
        $this->assertEquals('Success get all products', $response['message']);

        $createdAtTimes = array_map(function ($product) {
            return \Carbon\Carbon::parse($product['created_at'])->format('Y-m-d\TH:i:s.000000\Z');
        }, $response['data']);

        $this->assertEquals([$twoDaysAgo, $oneDayAgo, $today], $createdAtTimes);
    }

    public function testGetProductByIdSuccess()
    {
        $product = Product::factory()->create();

        $response = $this->productService->getProductById($product->id);

        $this->assertTrue($response['status']);
        $this->assertEquals('Successfully Get Single Product', $response['message']);
        $this->assertEquals($product->toArray(), $response['data']);
    }

    public function testGetProductByIdNotFound()
    {
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Product Not Found');

        $this->productService->getProductById(99999);
    }

    public function testCreateProductSuccess()
    {
        $category = Category::factory()->create();

        $image = UploadedFile::fake()->image('product.jpg');
        $data = [
            'name' => 'Test Product',
            'id_category' => $category->id,
            'description' => 'Test description',
            'price' => 340,
            'image' => $image,
        ];

        $response = $this->productService->createProduct($data);

        $this->assertTrue($response['status']);
        $this->assertEquals('Product Created Successfully', $response['message']);

        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'description' => 'Test description',
            'price' => 340,
            'id_category' => $category->id,
        ]);

        $this->assertTrue(Storage::disk('public')->exists('products/' . $image->hashName()));
    }

    public function testCreateProductValidationFailure()
    {
        $data = [];

        $this->expectException(ValidationException::class);
        $this->productService->createProduct($data);
    }

    public function testUpdateProductSuccess()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'id_category' => $category->id,
            'price' => 340,
        ]);

        $newImage = UploadedFile::fake()->image('new-product.jpg');
        $data = [
            'name' => 'Updated Product',
            'id_category' => $category->id,
            'description' => 'Updated description',
            'price' => 400,
            'image' => $newImage,
        ];

        $response = $this->productService->updateProduct($product->id, $data);

        $this->assertTrue($response['status']);
        $this->assertEquals('Product Updated Successfully', $response['message']);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product',
            'description' => 'Updated description',
            'price' => 400,
            'id_category' => $category->id,
        ]);

        $newImagePath = $response['data']->image;
        $this->assertTrue(Storage::disk('public')->exists($newImagePath));

        $oldImagePath = $product->getOriginal('image');
        $this->assertFalse(Storage::disk('public')->exists($oldImagePath));
    }

    public function testUpdateProductNotFound()
    {
        // Arrange: Create a category
        Category::factory()->create();

        // Prepare new data
        $data = [
            'name' => 'Updated Product',
        ];

        // Act & Assert: Check ModelNotFoundException
        $this->expectException(ModelNotFoundException::class);
        $this->productService->updateProduct(9999, $data); // Non-existent product ID
    }

    public function testUpdateProductValidationFailure()
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'id_category' => $category->id,
            'price' => 340,
        ]);

        $data = [
            'id_category' => 9999,
        ];

        $this->expectException(ValidationException::class);
        $this->productService->updateProduct($product->id, $data);
    }

    public function testDeleteProduct()
    {
        $category = Category::create(['name' => 'Category for Product']);
        $product = Product::create([
            'name' => 'Product to Delete',
            'id_category' => $category->id,
            'description' => 'Description of product to delete',
            'price' => 100,
            'image' => 'products/sample.jpg',
        ]);

        $response = $this->productService->deleteProduct($product->id);

        $this->assertTrue($response['status']);
        $this->assertEquals('Product Deleted Successfully', $response['message']);

        $this->assertNull(Product::find($product->id));

        $this->assertFalse(Storage::disk('public')->exists($product->image));
    }

    public function testDeleteProductNotFound()
    {
        $this->expectException(ModelNotFoundException::class);

        $this->productService->deleteProduct(999);
    }
}
