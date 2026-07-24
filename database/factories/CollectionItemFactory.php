<?php

namespace Database\Factories;

use App\Models\Collection;
use App\Models\CollectionItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CollectionItem>
 */
class CollectionItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'collection_id' => Collection::factory(),
            'image_path' => 'collection-items/'.fake()->uuid().'.jpg',
            'name' => fake()->optional()->randomElement(['Hand grinder', 'Pour-over brewer', 'Gooseneck kettle', 'Coffee scale']),
            'quantity' => fake()->numberBetween(1, 3),
            'notes' => fake()->optional()->sentence(),
            'rating' => fake()->optional()->randomElement([0.5, 1, 1.5, 2, 2.5, 3, 3.5, 4, 4.5, 5]),
        ];
    }
}
