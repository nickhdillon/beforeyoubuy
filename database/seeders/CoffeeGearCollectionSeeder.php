<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\User;
use Illuminate\Database\Seeder;

class CoffeeGearCollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::query()->where('email', 'test@example.com')->first()
            ?? User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);

        $collections = [
            ['name' => 'Daily Brew Bar', 'description' => 'The grinders, brewers, scales, and kettles I reach for every morning.', 'is_public' => true],
            ['name' => 'Espresso Setup', 'description' => 'A compact home espresso station built around consistent, repeatable shots.', 'is_public' => true],
            ['name' => 'Travel Coffee Kit', 'description' => 'A hand grinder, compact brewer, and sturdy mug for good coffee away from home.', 'is_public' => false],
        ];

        foreach ($collections as $attributes) {
            $collection = Collection::query()->firstOrCreate(
                ['user_id' => $user->id, 'name' => $attributes['name']],
                $attributes,
            );

            $collection->wishlist()->firstOrCreate();
        }
    }
}
