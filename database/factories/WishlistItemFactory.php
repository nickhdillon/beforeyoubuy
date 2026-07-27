<?php

namespace Database\Factories;

use App\Models\Collection;
use App\Models\WishlistItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WishlistItem>
 */
class WishlistItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'wishlist_id' => fn (): int => Collection::factory()->create()->wishlist()->sole()->id,
            'image_path' => 'wishlist-items/'.fake()->uuid().'.jpg',
            'name' => fake()->optional()->words(3, true),
            'url' => fake()->optional()->url(),
            'notes' => fake()->optional()->sentence(),
            'quantity' => fake()->numberBetween(1, 3),
            'rating' => fake()->optional()->randomElement([0.5, 1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5]),
        ];
    }
}
