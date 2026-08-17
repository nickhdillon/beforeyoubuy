<?php

use App\Livewire\CollectionItems\Index as CollectionItemsIndex;
use App\Livewire\Collections\Index as CollectionsIndex;
use App\Livewire\WishlistItems\Index as WishlistItemsIndex;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\Tag;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\Blade;
use Livewire\Livewire;

test('the input clear control keeps the default Flux utility styling', function () {
    $html = Blade::render('<flux:input value="Coffee" clearable />');

    preg_match('/<button(?=[^>]*data-flux-clear-button)[^>]*>/', $html, $matches);

    expect($matches)->toHaveCount(1)
        ->and($matches[0])->toContain('data-flux-button-utility')
        ->and($matches[0])->not->toContain('hard-shadow');
});

test('collections can be searched by their details and item metadata', function () {
    $user = User::factory()->create();
    $coffeeCollection = Collection::factory()->for($user)->create([
        'name' => 'Coffee gear',
        'description' => 'Tools for better mornings',
    ]);
    $cameraCollection = Collection::factory()->for($user)->create(['name' => 'Film cameras']);
    $tag = Tag::factory()->for($user)->create(['name' => 'Travel']);
    $taggedItem = CollectionItem::factory()->for($cameraCollection)->create(['name' => 'Pocket camera']);
    $taggedItem->tags()->attach($tag);

    Livewire::actingAs($user)
        ->test(CollectionsIndex::class)
        ->set('search', 'mornings')
        ->assertSee($coffeeCollection->name)
        ->assertDontSee($cameraCollection->name)
        ->set('search', 'Travel')
        ->assertSee($cameraCollection->name)
        ->assertDontSee($coffeeCollection->name)
        ->assertSet('search', 'Travel');
});

test('collection items can be searched by name notes link and tags', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();
    $matchingItem = CollectionItem::factory()->for($collection)->create([
        'name' => 'Hand grinder',
        'notes' => 'Great for camping',
        'url' => 'https://example.com/grinder',
    ]);
    $otherItem = CollectionItem::factory()->for($collection)->create(['name' => 'Coffee scale']);
    $tag = Tag::factory()->for($user)->create(['name' => 'Portable']);
    $matchingItem->tags()->attach($tag);

    $component = Livewire::actingAs($user)->test(CollectionItemsIndex::class, ['collection' => $collection]);

    foreach (['grinder', 'camping', 'example.com', 'portable'] as $search) {
        $component->set('search', $search)
            ->assertSee($matchingItem->name)
            ->assertDontSee($otherItem->name);
    }

    $component->set('search', 'missing')
        ->assertSee('No matching items')
        ->assertSee('Clear search');
});

test('wishlist items can be searched without exposing another collection wishlist', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();
    $matchingItem = WishlistItem::factory()->for($collection->wishlist)->create([
        'name' => 'Gooseneck kettle',
        'notes' => 'Wait for a sale',
    ]);
    $otherItem = WishlistItem::factory()->for($collection->wishlist)->create(['name' => 'Espresso machine']);
    $otherCollection = Collection::factory()->create(['name' => 'Another wishlist collection']);
    WishlistItem::factory()->for($otherCollection->wishlist)->create(['name' => 'Gooseneck kettle from another wishlist']);
    $tag = Tag::factory()->for($user)->create(['name' => 'Upgrade']);
    $matchingItem->tags()->attach($tag);

    Livewire::actingAs($user)
        ->test(WishlistItemsIndex::class, ['collection' => $collection])
        ->set('search', 'upgrade')
        ->assertSee($matchingItem->name)
        ->assertDontSee($otherItem->name)
        ->assertDontSee('Gooseneck kettle from another wishlist')
        ->set('search', 'sale')
        ->assertSee($matchingItem->name);
});
