<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;


class CategoryControllerTest extends TestCase
{

    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an admin and a regular user with 'role' instead of 'is_admin'
        $this->admin = User::factory()->admin()->create();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function admin_can_create_category()
    {
        $response = $this->actingAs($this->admin)
                         ->post('/api/category', [
                             'name' => 'New Category'
                         ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseHas('categories', ['name' => 'New Category']);
    }

    /** @test */
    public function regular_user_cannot_create_category()
    {
        $response = $this->actingAs($this->user)
                         ->post('/api/category', [
                             'name' => 'New Category'
                         ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function admin_can_update_category()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)
                         ->put("/api/category/{$category->id}", [
                             'name' => 'Updated Category'
                         ]);

        $response->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseHas('categories', ['name' => 'Updated Category']);
    }

    /** @test */
    public function regular_user_cannot_update_category()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)
                         ->put("/api/category/{$category->id}", [
                             'name' => 'Updated Category'
                         ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function admin_can_delete_category()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)
                         ->delete("/api/category/{$category->id}");

        $response->assertStatus(Response::HTTP_OK);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    /** @test */
    public function regular_user_cannot_delete_category()
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)
                         ->delete("/api/category/{$category->id}");

        $response->assertStatus(Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function anyone_can_view_all_categories()
    {
        Category::factory()->create(['name' => 'Category 1']);
        Category::factory()->create(['name' => 'Category 2']);

        $response = $this->get('/api/category');

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment(['name' => 'Category 1']);
        $response->assertJsonFragment(['name' => 'Category 2']);
    }

    /** @test */
    public function anyone_can_view_a_single_category()
    {
        $category = Category::factory()->create(['name' => 'Category 1']);

        $response = $this->get("/api/category/{$category->id}");

        $response->assertStatus(Response::HTTP_OK);
        $response->assertJsonFragment(['name' => 'Category 1']);
    }

}
