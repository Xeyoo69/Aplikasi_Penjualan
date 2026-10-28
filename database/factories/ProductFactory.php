<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    public function definition(): array
    {
        $purchasePrice = fake()->numberBetween(2_000, 100_000);

        return [
            'category_id' => Category::factory(),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####-??')),
            'barcode' => fake()->unique()->numerify('899##########'),
            'name' => ucfirst(fake()->unique()->words(3, true)),
            'purchase_price' => $purchasePrice,
            'selling_price' => $purchasePrice + fake()->numberBetween(500, 20_000),
            'stock' => fake()->numberBetween(20, 100),
            'min_stock' => 5,
            'image' => null,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }

    public function lowStock(): static
    {
        return $this->state(fn () => ['stock' => 2, 'min_stock' => 5]);
    }
}
