<?php

use App\Livewire\CollectionItems\Index as CollectionItemsIndex;
use App\Livewire\Collections\Index as CollectionsIndex;
use App\Livewire\WishlistItems\Index as WishlistItemsIndex;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\Tag;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

test('filter controls use Flux flyouts and non-native selects', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();

    $components = [
        Livewire::actingAs($user)->test(CollectionsIndex::class),
        Livewire::actingAs($user)->test(CollectionItemsIndex::class, ['collection' => $collection]),
        Livewire::actingAs($user)->test(WishlistItemsIndex::class, ['collection' => $collection]),
    ];

    foreach ($components as $component) {
        expect($component->html())
            ->toContain('data-flux-flyout')
            ->toContain('data-flux-select')
            ->not->toContain('data-flux-select-native');
    }
});

test('the collection page does not execute duplicate select queries', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();
    Tag::factory()->for($user)->create();
    CollectionItem::factory()->count(2)->for($collection)->create();

    DB::flushQueryLog();
    DB::enableQueryLog();

    $this->actingAs($user)
        ->get(route('collections.show', $collection))
        ->assertSuccessful();

    $tagQueries = collect(DB::getQueryLog())
        ->pluck('query')
        ->filter(fn (string $query): bool => str_contains($query, 'from "tags"') && str_contains($query, '"user_id"'));

    $duplicateSelects = collect(DB::getQueryLog())
        ->filter(fn (array $query): bool => str_starts_with($query['query'], 'select'))
        ->groupBy(fn (array $query): string => $query['query'].json_encode($query['bindings']))
        ->filter(fn ($queries): bool => $queries->count() > 1);

    expect($tagQueries)->toHaveCount(1)
        ->and($duplicateSelects)->toBeEmpty();
});

test('the collections list does not eager load unused wishlists', function () {
    $user = User::factory()->create();
    Collection::factory()->count(2)->for($user)->create();

    DB::enableQueryLog();

    Livewire::actingAs($user)->test(CollectionsIndex::class);

    $eagerLoadQueries = collect(DB::getQueryLog())
        ->pluck('query')
        ->filter(fn (string $query): bool => str_contains($query, 'from "wishlists"') && str_contains($query, ' in ('));

    expect($eagerLoadQueries)->toBeEmpty();
});

test('filter changes are staged until results are requested', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();
    CollectionItem::factory()->for($collection)->create(['name' => 'One ceramic cup', 'quantity' => 1]);
    CollectionItem::factory()->for($collection)->create(['name' => 'Pair of glass cups', 'quantity' => 2]);

    Livewire::actingAs($user)
        ->test(CollectionItemsIndex::class, ['collection' => $collection])
        ->call('prepareFilters')
        ->set('filterDraft.quantity', 'multiple')
        ->assertSet('quantity', '')
        ->assertSee('One ceramic cup')
        ->assertSee('Pair of glass cups')
        ->call('applyFilters')
        ->assertSet('quantity', 'multiple')
        ->assertDontSee('One ceramic cup')
        ->assertSee('Pair of glass cups')
        ->set('filterDraft.quantity', 'single')
        ->call('prepareFilters')
        ->assertSet('filterDraft.quantity', 'multiple');
});

test('collection items can be filtered by tag rating quantity and link', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();
    $tag = Tag::factory()->for($user)->create(['name' => 'Favorite']);
    $match = CollectionItem::factory()->for($collection)->create([
        'name' => 'Matching grinder',
        'rating' => 4.5,
        'quantity' => 2,
        'url' => 'https://example.com/grinder',
    ]);
    $match->tags()->attach($tag);
    CollectionItem::factory()->for($collection)->create([
        'name' => 'Other grinder',
        'rating' => 3,
        'quantity' => 1,
        'url' => null,
    ]);

    Livewire::actingAs($user)
        ->test(CollectionItemsIndex::class, ['collection' => $collection])
        ->set('tagId', (string) $tag->id)
        ->set('minimumRating', '4')
        ->set('quantity', 'multiple')
        ->set('link', 'with')
        ->assertSee('Matching grinder')
        ->assertDontSee('Other grinder')
        ->call('clearFilters')
        ->assertSet('tagId', '')
        ->assertSet('minimumRating', '')
        ->assertSet('quantity', '')
        ->assertSet('link', '')
        ->assertSee('Other grinder');
});

test('wishlist items can be filtered by metadata and sorted', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();
    $withPhoto = WishlistItem::factory()->for($collection->wishlist)->create([
        'name' => 'Photo item',
        'image_path' => 'wishlist-items/photo.jpg',
        'rating' => 5,
        'quantity' => 3,
    ]);
    WishlistItem::factory()->for($collection->wishlist)->create([
        'name' => 'No photo item',
        'image_path' => null,
        'rating' => 2,
        'quantity' => 1,
    ]);

    Livewire::actingAs($user)
        ->test(WishlistItemsIndex::class, ['collection' => $collection])
        ->set('photo', 'with')
        ->set('minimumRating', '4')
        ->set('quantity', 'multiple')
        ->assertSee($withPhoto->name)
        ->assertDontSee('No photo item')
        ->call('clearFilters')
        ->set('sort', 'name')
        ->assertSeeInOrder(['No photo item', 'Photo item']);
});

test('collections can be filtered by visibility and nested item metadata', function () {
    $user = User::factory()->create();
    $tag = Tag::factory()->for($user)->create(['name' => 'Travel']);
    $matchingCollection = Collection::factory()->for($user)->public()->create(['name' => 'Matching collection']);
    $otherCollection = Collection::factory()->for($user)->create(['name' => 'Other collection']);
    $wishlistItem = WishlistItem::factory()->for($matchingCollection->wishlist)->create([
        'rating' => 4.5,
        'quantity' => 2,
    ]);
    $wishlistItem->tags()->attach($tag);
    CollectionItem::factory()->for($otherCollection)->create(['rating' => 5, 'quantity' => 3]);

    Livewire::actingAs($user)
        ->test(CollectionsIndex::class)
        ->set('visibility', 'public')
        ->set('tagId', (string) $tag->id)
        ->set('minimumRating', '4')
        ->set('quantity', 'multiple')
        ->set('contents', 'wishlist')
        ->assertSee($matchingCollection->name)
        ->assertDontSee($otherCollection->name);
});

test('empty collection filtering excludes collections with wishlist or collected items', function () {
    $user = User::factory()->create();
    $emptyCollection = Collection::factory()->for($user)->create(['name' => 'Empty collection']);
    $collected = Collection::factory()->for($user)->create(['name' => 'Collected']);
    $wishlisted = Collection::factory()->for($user)->create(['name' => 'Wishlisted']);
    CollectionItem::factory()->for($collected)->create();
    WishlistItem::factory()->for($wishlisted->wishlist)->create();

    Livewire::actingAs($user)
        ->test(CollectionsIndex::class)
        ->set('contents', 'empty')
        ->assertSee($emptyCollection->name)
        ->assertDontSee($collected->name)
        ->assertDontSee($wishlisted->name);
});
