<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        // Ensure the storage directory is cleared and create a fake image
        Storage::fake('public');
        $image = UploadedFile::fake()->image('product.jpg');

        return [
            'name' => $this->faker->word,
            'id_category' => Category::factory(),
            'description' => $this->faker->sentence,
            'price' => $this->faker->numberBetween(10, 1000),
            'image' => $image->store('products', 'public'), // Save fake image to storage
        ];
    }
}
