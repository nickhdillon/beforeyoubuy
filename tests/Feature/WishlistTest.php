<?php

use App\Livewire\CollectionItems\Index as CollectionItemsIndex;
use App\Livewire\WishlistItems\Index as WishlistItemsIndex;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\User;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Policies\WishlistItemPolicy;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('every collection automatically receives exactly one wishlist', function () {
    $collection = Collection::factory()->create();

    expect($collection->wishlist()->count())->toBe(1)
        ->and(Wishlist::query()->whereBelongsTo($collection)->count())->toBe(1);
});

test('a wishlist contains items and is deleted with its collection', function () {
    $collection = Collection::factory()->create();
    $wishlist = $collection->wishlist()->sole();
    $item = WishlistItem::factory()->for($wishlist)->create();

    expect($wishlist->items->first()->is($item))->toBeTrue();

    $collection->delete();

    $this->assertModelMissing($wishlist);
    $this->assertModelMissing($item);
});

test('wishlist items are owner only even when their collection is public', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $collection = Collection::factory()->for($owner)->public()->create();
    $wishlist = $collection->wishlist()->sole();
    $item = WishlistItem::factory()->for($wishlist)->create(['name' => 'Secret grinder']);
    $policy = app(WishlistItemPolicy::class);

    expect($policy->view($owner, $item)->allowed())->toBeTrue()
        ->and($policy->view($otherUser, $item)->status())->toBe(404)
        ->and($policy->view(null, $item)->status())->toBe(404)
        ->and($policy->create($owner, $wishlist)->allowed())->toBeTrue()
        ->and($policy->create($otherUser, $wishlist)->status())->toBe(404);

    $this->actingAs($owner)
        ->get(route('collections.show', $collection))
        ->assertOk()
        ->assertSee('Secret grinder')
        ->assertSee('Your wishlist is visible only to you');

    $this->actingAs($otherUser)
        ->get(route('collections.public', ['user' => $owner, 'collection' => $collection]))
        ->assertOk()
        ->assertDontSee('Secret grinder')
        ->assertDontSee('Add wishlist item');

    $this->get(route('collections.public', ['user' => $owner, 'collection' => $collection]))
        ->assertOk()
        ->assertDontSee('Secret grinder')
        ->assertDontSee('Add wishlist item');
});

test('item events refresh the computed collection and wishlist items', function () {
    $owner = User::factory()->create();
    $collection = Collection::factory()->for($owner)->create();

    $this->actingAs($owner);

    $collectionItems = Livewire::test(CollectionItemsIndex::class, ['collection' => $collection])
        ->assertDontSee('New collection item');

    $wishlistItems = Livewire::test(WishlistItemsIndex::class, ['collection' => $collection])
        ->assertDontSee('New wishlist item');

    CollectionItem::factory()->for($collection)->create(['name' => 'New collection item']);
    WishlistItem::factory()->for($collection->wishlist)->create(['name' => 'New wishlist item']);

    $collectionItems
        ->dispatch('collection-item-created')
        ->assertSee('New collection item');

    $wishlistItems
        ->dispatch('wishlist-item-created')
        ->assertSee('New wishlist item');
});

test('an owner can move a collection item to its private wishlist after confirmation', function () {
    Storage::fake('public');
    Storage::disk('public')->put('collection-items/grinder.jpg', 'photo');

    $owner = User::factory()->create();
    $collection = Collection::factory()->for($owner)->create();
    $item = CollectionItem::factory()->for($collection)->create([
        'image_path' => 'collection-items/grinder.jpg',
        'name' => 'Coffee grinder',
        'url' => 'https://example.com/grinder',
        'quantity' => 2,
        'rating' => 4.5,
        'notes' => 'Daily driver.',
    ]);

    $this->actingAs($owner);

    Livewire::test(CollectionItemsIndex::class, ['collection' => $collection])
        ->assertSee('aria-label="Edit Coffee grinder"', false)
        ->assertSee('aria-label="Actions for Coffee grinder"', false)
        ->assertSee('Move to wishlist')
        ->assertSee('data-modal="move-item-to-wishlist"', false)
        ->call('confirmMoveToWishlist', $item->id)
        ->assertSet('pendingItem', fn (?CollectionItem $pendingItem): bool => $pendingItem?->is($item) === true)
        ->assertSee('Move Coffee grinder to this collection’s private wishlist?')
        ->call('moveToWishlist')
        ->assertSet('pendingItem', null);

    $wishlistItem = WishlistItem::query()->sole();

    $this->assertModelMissing($item);

    expect($wishlistItem->wishlist->is($collection->wishlist))->toBeTrue()
        ->and($wishlistItem->image_path)->not->toBe($item->image_path)
        ->and($wishlistItem->name)->toBe($item->name)
        ->and($wishlistItem->url)->toBe($item->url)
        ->and($wishlistItem->quantity)->toBe($item->quantity)
        ->and($wishlistItem->rating)->toBe($item->rating)
        ->and($wishlistItem->notes)->toBe($item->notes);

    Storage::disk('public')->assertMissing($item->image_path);
    Storage::disk('public')->assertExists($wishlistItem->image_path);
});

test('another user cannot move a public collection item to its wishlist', function () {
    Storage::fake('public');
    Storage::disk('public')->put('collection-items/grinder.jpg', 'photo');

    $collection = Collection::factory()->public()->create();
    $item = CollectionItem::factory()->for($collection)->create([
        'image_path' => 'collection-items/grinder.jpg',
    ]);

    $this->actingAs(User::factory()->create());

    Livewire::test(CollectionItemsIndex::class, ['collection' => $collection])
        ->assertDontSee('Move to wishlist')
        ->call('confirmMoveToWishlist', $item->id)
        ->assertNotFound();

    Livewire::test(CollectionItemsIndex::class, ['collection' => $collection])
        ->assertDontSee('Delete item')
        ->call('confirmDelete', $item->id)
        ->assertNotFound();

    expect(WishlistItem::query()->doesntExist())->toBeTrue();
});

test('an owner can delete a collection item from its card menu after confirmation', function () {
    Storage::fake('public');
    Storage::disk('public')->put('collection-items/grinder.jpg', 'photo');

    $owner = User::factory()->create();
    $collection = Collection::factory()->for($owner)->create();
    $item = CollectionItem::factory()->for($collection)->create([
        'image_path' => 'collection-items/grinder.jpg',
        'name' => 'Coffee grinder',
    ]);

    $this->actingAs($owner);

    Livewire::test(CollectionItemsIndex::class, ['collection' => $collection])
        ->assertSee('Delete item')
        ->assertSee('data-modal="delete-collection-item-from-card"', false)
        ->call('confirmDelete', $item->id)
        ->assertSet('pendingItem', fn (?CollectionItem $pendingItem): bool => $pendingItem?->is($item) === true)
        ->assertSee('Permanently delete Coffee grinder and its photo?')
        ->call('delete')
        ->assertSet('pendingItem', null)
        ->assertDontSee('Coffee grinder');

    $this->assertModelMissing($item);
    Storage::disk('public')->assertMissing('collection-items/grinder.jpg');
});

test('an owner can move a wishlist item to its collection after confirmation', function () {
    Storage::fake('public');
    Storage::disk('public')->put('wishlist-items/grinder.jpg', 'photo');

    $owner = User::factory()->create();
    $collection = Collection::factory()->for($owner)->create();
    $wishlistItem = WishlistItem::factory()->for($collection->wishlist)->create([
        'image_path' => 'wishlist-items/grinder.jpg',
        'name' => 'Coffee grinder',
        'url' => 'https://example.com/grinder',
        'quantity' => 2,
        'rating' => 4.5,
        'notes' => 'Buy when the current grinder wears out.',
    ]);

    $this->actingAs($owner);

    Livewire::test(WishlistItemsIndex::class, ['collection' => $collection])
        ->assertSee('aria-label="Edit Coffee grinder"', false)
        ->assertSee('aria-label="Actions for Coffee grinder"', false)
        ->assertSee('Move to collection')
        ->assertSee('data-modal="move-wishlist-item-to-collection"', false)
        ->call('confirmMoveToCollection', $wishlistItem->id)
        ->assertSet('pendingItem', fn (?WishlistItem $pendingItem): bool => $pendingItem?->is($wishlistItem) === true)
        ->assertSee('Move Coffee grinder to this collection?')
        ->call('moveToCollection')
        ->assertSet('pendingItem', null);

    $collectionItem = CollectionItem::query()->sole();

    $this->assertModelMissing($wishlistItem);

    expect($collectionItem->collection->is($collection))->toBeTrue()
        ->and($collectionItem->image_path)->not->toBe($wishlistItem->image_path)
        ->and($collectionItem->name)->toBe($wishlistItem->name)
        ->and($collectionItem->url)->toBe($wishlistItem->url)
        ->and($collectionItem->quantity)->toBe($wishlistItem->quantity)
        ->and($collectionItem->rating)->toBe($wishlistItem->rating)
        ->and($collectionItem->notes)->toBe($wishlistItem->notes);

    Storage::disk('public')->assertMissing($wishlistItem->image_path);
    Storage::disk('public')->assertExists($collectionItem->image_path);
});

test('an owner can delete a wishlist item from its card menu after confirmation', function () {
    Storage::fake('public');
    Storage::disk('public')->put('wishlist-items/grinder.jpg', 'photo');

    $owner = User::factory()->create();
    $collection = Collection::factory()->for($owner)->create();
    $wishlistItem = WishlistItem::factory()->for($collection->wishlist)->create([
        'image_path' => 'wishlist-items/grinder.jpg',
        'name' => 'Coffee grinder',
    ]);

    $this->actingAs($owner);

    Livewire::test(WishlistItemsIndex::class, ['collection' => $collection])
        ->assertSee('Delete item')
        ->assertSee('data-modal="delete-wishlist-item-from-card"', false)
        ->call('confirmDelete', $wishlistItem->id)
        ->assertSet('pendingItem', fn (?WishlistItem $pendingItem): bool => $pendingItem?->is($wishlistItem) === true)
        ->assertSee('Permanently delete Coffee grinder from your wishlist?')
        ->call('delete')
        ->assertSet('pendingItem', null)
        ->assertDontSee('Coffee grinder');

    $this->assertModelMissing($wishlistItem);
    Storage::disk('public')->assertMissing('wishlist-items/grinder.jpg');
});

test('another user cannot delete a wishlist item through the card action', function () {
    $owner = User::factory()->create();
    $collection = Collection::factory()->for($owner)->public()->create();
    $wishlistItem = WishlistItem::factory()->for($collection->wishlist)->create();

    $this->actingAs(User::factory()->create());

    Livewire::test(WishlistItemsIndex::class, ['collection' => $collection])
        ->assertNotFound();

    $this->assertModelExists($wishlistItem);
});
