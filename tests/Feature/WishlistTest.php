<?php

use App\Livewire\Collections\Show;
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

    expect($policy->view($owner, $item))->toBeTrue()
        ->and($policy->view($otherUser, $item))->toBeFalse()
        ->and($policy->view(null, $item))->toBeFalse()
        ->and($policy->create($owner, $wishlist))->toBeTrue()
        ->and($policy->create($otherUser, $wishlist))->toBeFalse();

    $this->actingAs($owner)
        ->get(route('collections.show', $collection))
        ->assertOk()
        ->assertSee('Secret grinder')
        ->assertSee('Your wishlist is visible only to you');

    $this->actingAs($otherUser)
        ->get(route('collections.show', $collection))
        ->assertOk()
        ->assertDontSee('Secret grinder')
        ->assertDontSee('Add wishlist item');

    $this->get(route('collections.show', $collection))
        ->assertOk()
        ->assertDontSee('Secret grinder')
        ->assertDontSee('Add wishlist item');
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

    Livewire::test(Show::class, ['collection' => $collection])
        ->assertSee('aria-label="Edit Coffee grinder"', false)
        ->assertSee('aria-label="Actions for Coffee grinder"', false)
        ->assertSee('Move to wishlist')
        ->assertSee('data-modal="move-item-to-wishlist"', false)
        ->call('confirmMoveToWishlist', $item->id)
        ->assertSet('wishlistSourceItemId', $item->id)
        ->assertSee('Move Coffee grinder to this collection’s private wishlist?')
        ->call('moveToWishlist')
        ->assertSet('wishlistSourceItemId', null);

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

    Livewire::test(Show::class, ['collection' => $collection])
        ->assertDontSee('Move to wishlist')
        ->call('confirmMoveToWishlist', $item->id)
        ->assertForbidden();

    Livewire::test(Show::class, ['collection' => $collection])
        ->assertDontSee('Delete item')
        ->call('confirmDeleteCollectionItem', $item->id)
        ->assertForbidden();

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

    Livewire::test(Show::class, ['collection' => $collection])
        ->assertSee('Delete item')
        ->assertSee('data-modal="delete-collection-item-from-card"', false)
        ->call('confirmDeleteCollectionItem', $item->id)
        ->assertSet('collectionItemPendingDeletionId', $item->id)
        ->assertSee('Permanently delete Coffee grinder and its photo?')
        ->call('deleteCollectionItem')
        ->assertSet('collectionItemPendingDeletionId', null)
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

    Livewire::test(Show::class, ['collection' => $collection])
        ->assertSee('aria-label="Edit Coffee grinder"', false)
        ->assertSee('aria-label="Actions for Coffee grinder"', false)
        ->assertSee('Move to collection')
        ->assertSee('data-modal="move-wishlist-item-to-collection"', false)
        ->call('confirmMoveToCollection', $wishlistItem->id)
        ->assertSet('collectionSourceWishlistItemId', $wishlistItem->id)
        ->assertSee('Move Coffee grinder to this collection?')
        ->call('moveToCollection')
        ->assertSet('collectionSourceWishlistItemId', null);

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

    Livewire::test(Show::class, ['collection' => $collection])
        ->assertSee('Delete item')
        ->assertSee('data-modal="delete-wishlist-item-from-card"', false)
        ->call('confirmDeleteWishlistItem', $wishlistItem->id)
        ->assertSet('wishlistItemPendingDeletionId', $wishlistItem->id)
        ->assertSee('Permanently delete Coffee grinder from your wishlist?')
        ->call('deleteWishlistItem')
        ->assertSet('wishlistItemPendingDeletionId', null)
        ->assertDontSee('Coffee grinder');

    $this->assertModelMissing($wishlistItem);
    Storage::disk('public')->assertMissing('wishlist-items/grinder.jpg');
});

test('another user cannot delete a wishlist item through the card action', function () {
    $owner = User::factory()->create();
    $collection = Collection::factory()->for($owner)->public()->create();
    $wishlistItem = WishlistItem::factory()->for($collection->wishlist)->create();

    $this->actingAs(User::factory()->create());

    Livewire::test(Show::class, ['collection' => $collection])
        ->assertDontSee('Delete item')
        ->call('confirmDeleteWishlistItem', $wishlistItem->id)
        ->assertForbidden();

    $this->assertModelExists($wishlistItem);
});
