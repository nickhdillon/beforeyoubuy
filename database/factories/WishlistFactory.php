<?php

namespace Database\Factories;

use App\Models\Collection;
use App\Models\Wishlist;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wishlist>
 */
class WishlistFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'collection_id' => fn (): int => Collection::withoutEvents(
                fn (): Collection => Collection::factory()->create()
            )->id,
        ];
    }
}
