<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin and a regular user
        $this->admin = User::factory()->admin()->create();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function admin_can_create_product()
    {
        Storage::fake('public');

        $category = Category::factory()->create();
        $image = UploadedFile::fake()->image('product.jpg');

        $response = $this->actingAs($this->admin)
            ->post('/api/products', [
                'name' => 'New Product',
                'id_category' => $category->id,
                'description' => 'Product Description',
                'price' => 100,
                'image' => $image,
            ]);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertDatabaseHas('products', ['name' => 'New Product']);

        // Check if the file exists in the storage
        $this->assertTrue(Storage::disk('public')->exists('products/' . $image->hashName()));
    }

    /** @test */
    public function regular_user_cannot_create_product()
    {
        $category = Category::factory()->create();
        $image = UploadedFile::fake()->image('product.jpg');

        $response = $this->actingAs($this->user)
            ->post('/api/products', [
                'name' => 'New Product',
                'id_category' => $category->id,
                'description' => 'Product Description',
                'price' => 100,
                'image' => $image,
            ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function admin_can_update_product()
    {
        Storage::fake('public');

        $product = Product::factory()->create();

        // Create a fake image directly
        $image = UploadedFile::fake()->image('product.jpg');

        $response = $this->actingAs($this->admin)
            ->put("/api/products/{$product->id}", [
                'name' => 'Updated Product Name',
                'id_category' => $product->id_category,
                'description' => 'Updated Description',
                'price' => 150,
                'image' => $image,
            ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseHas('products', ['name' => 'Updated Product Name']);

        // Check if the uploaded file exists in the fake storage
        $this->assertTrue(Storage::disk('public')->exists('products/' . $image->hashName()));
    }

    /** @test */
    public function regular_user_cannot_update_product()
    {
        $product = Product::factory()->create();
        $image = UploadedFile::fake()->image('new_product.jpg');

        $response = $this->actingAs($this->user)
            ->put("/api/products/{$product->id}", [
                'name' => 'Updated Product Name',
                'id_category' => $product->id_category,
                'description' => 'Updated Description',
                'price' => 150,
                'image' => $image,
            ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function admin_can_delete_product()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete("/api/products/{$product->id}");

        $response->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    /** @test */
    public function regular_user_cannot_delete_product()
    {
        $product = Product::factory()->create();

        $response = $this->actingAs($this->user)
            ->delete("/api/products/{$product->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function anyone_can_view_all_products()
    {
        Product::factory()->create(['name' => 'Product 1']);
        Product::factory()->create(['name' => 'Product 2']);

        $response = $this->get('/api/products');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment(['name' => 'Product 1']);
        $response->assertJsonFragment(['name' => 'Product 2']);
    }

    /** @test */
    public function anyone_can_view_a_single_product()
    {
        $product = Product::factory()->create(['name' => 'Product 1']);

        $response = $this->get("/api/products/{$product->id}");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment(['name' => 'Product 1']);
    }
}
