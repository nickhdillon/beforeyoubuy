<?php

use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\User;
use App\Models\Wishlist;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\UniqueConstraintViolationException;

test('a collection belongs to a user and has one wishlist', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();
    $wishlist = $collection->wishlist()->sole();

    expect($collection->user->is($user))->toBeTrue()
        ->and($collection->wishlist->is($wishlist))->toBeTrue()
        ->and($collection->slug)->not->toBeEmpty()
        ->and($collection->getRouteKeyName())->toBe('slug');
});

test('user slugs are generated from names and remain globally unique', function () {
    $firstUser = User::factory()->create(['name' => 'Nick Dillon']);
    $secondUser = User::factory()->create(['name' => 'Nick Dillon']);

    expect($firstUser->slug)->toBe('nick-dillon')
        ->and($secondUser->slug)->toBe('nick-dillon-2');
});

test('collection slugs stay unique and update with their names', function () {
    $firstCollection = Collection::factory()->create(['name' => 'Coffee Gear']);
    $secondCollection = Collection::factory()->create(['name' => 'Coffee Gear 2']);

    expect($firstCollection->slug)->toBe('coffee-gear')
        ->and($secondCollection->slug)->toBe('coffee-gear-2');

    $firstCollection->update(['name' => 'Daily Setup']);

    expect($firstCollection->fresh()->slug)->toBe('daily-setup');
});

test('collection names are unique per user at the database level', function () {
    $user = User::factory()->create();

    Collection::factory()->for($user)->create(['name' => 'Coffee Gear']);
    Collection::factory()->create(['name' => 'Coffee Gear']);

    expect(fn () => Collection::factory()->for($user)->create(['name' => 'Coffee Gear']))
        ->toThrow(UniqueConstraintViolationException::class);
});

test('a collection last updated timestamp includes its latest item update', function () {
    $collection = Collection::factory()->create([
        'updated_at' => '2026-01-01 12:00:00',
    ]);

    CollectionItem::factory()->for($collection)->create([
        'updated_at' => '2026-01-03 12:00:00',
    ]);

    CollectionItem::factory()->for($collection)->create([
        'updated_at' => '2026-01-02 12:00:00',
    ]);

    expect($collection->load('items')->last_updated_at->toDateTimeString())
        ->toBe('2026-01-03 12:00:00');
});

test('the collections page only shows collections owned by the signed in user', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create(['name' => 'My Coffee Gear']);
    Collection::factory()->create(['name' => 'Someone Else’s Gear']);

    $this->actingAs($user)
        ->get(route('collections.index'))
        ->assertOk()
        ->assertSee('My Coffee Gear')
        ->assertSee('href="'.route('collections.show', $collection).'"', false)
        ->assertDontSee('Someone Else’s Gear');
});

test('guests cannot open the collections management page', function () {
    $this->get(route('collections.index'))->assertRedirect(route('login'));
});

test('the coffee gear seeder creates slugs and can be safely rerun', function () {
    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(User::query()->where('email', 'test@example.com')->count())->toBe(1)
        ->and(Collection::query()->count())->toBe(3)
        ->and(Wishlist::query()->count())->toBe(3)
        ->and(Collection::query()->whereNull('slug')->doesntExist())->toBeTrue();
});
