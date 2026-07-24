<?php

use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\User;
use App\Policies\CollectionPolicy;
use App\Policies\WishlistPolicy;

test('public collections can be viewed by anyone while private collections are owner only', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $publicCollection = Collection::factory()->for($owner)->public()->create(['name' => 'Public collection']);
    $privateCollection = Collection::factory()->for($owner)->create(['name' => 'Private collection']);
    $policy = app(CollectionPolicy::class);

    expect($policy->view(null, $publicCollection))->toBeTrue()
        ->and($policy->view($otherUser, $publicCollection))->toBeTrue()
        ->and($policy->view($owner, $privateCollection))->toBeTrue()
        ->and($policy->view($otherUser, $privateCollection))->toBeFalse()
        ->and($policy->view(null, $privateCollection))->toBeFalse();
});

test('only an owner may change their collection', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $collection = Collection::factory()->for($owner)->create();
    $policy = app(CollectionPolicy::class);

    expect($policy->update($owner, $collection))->toBeTrue()
        ->and($policy->delete($owner, $collection))->toBeTrue()
        ->and($policy->update($otherUser, $collection))->toBeFalse()
        ->and($policy->delete($otherUser, $collection))->toBeFalse();
});

test('wishlists stay private even when their collection is public', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $collection = Collection::factory()->for($owner)->public()->create();
    $wishlist = $collection->wishlist()->create();
    $policy = app(WishlistPolicy::class);

    expect($policy->view($owner, $wishlist))->toBeTrue()
        ->and($policy->view($otherUser, $wishlist))->toBeFalse()
        ->and($policy->view(null, $wishlist))->toBeFalse()
        ->and($policy->update($otherUser, $wishlist))->toBeFalse();
});

test('a public collection page is available to guests without exposing its wishlist', function () {
    $collection = Collection::factory()->public()->create(['name' => 'Shared Coffee Gear']);
    $collection->wishlist()->create();

    $this->get(route('collections.show', $collection))
        ->assertOk()
        ->assertSee('Shared Coffee Gear')
        ->assertSee('wishlist is private');
});

test('an owner can copy the public collection link', function () {
    $owner = User::factory()->create();
    $publicCollection = Collection::factory()->for($owner)->public()->create(['name' => 'Public coffee gear']);
    $privateCollection = Collection::factory()->for($owner)->create(['name' => 'Private coffee gear']);

    $this->actingAs($owner)
        ->get(route('collections.show', $publicCollection))
        ->assertOk()
        ->assertSee('aria-label="Copy public link"', false)
        ->assertSee('Copied to clipboard')
        ->assertSee('navigator.clipboard.writeText', false)
        ->assertSee($publicCollection->slug, false);

    $this->get(route('collections.show', $privateCollection))
        ->assertOk()
        ->assertDontSee('aria-label="Copy public link"', false)
        ->assertDontSee('Copied to clipboard')
        ->assertDontSee('navigator.clipboard.writeText', false);
});

test('public link controls are hidden from guests', function () {
    $collection = Collection::factory()->public()->create();

    $this->get(route('collections.show', $collection))
        ->assertOk()
        ->assertDontSee('aria-label="Copy public link"', false)
        ->assertDontSee('Copied to clipboard')
        ->assertDontSee('navigator.clipboard.writeText', false);
});

test('a private collection page is unavailable to guests and other users', function () {
    $collection = Collection::factory()->create();

    $this->get(route('collections.show', $collection))->assertForbidden();

    $this->actingAs(User::factory()->create())
        ->get(route('collections.show', $collection))
        ->assertForbidden();
});

test('only an owner sees collection items as edit buttons', function () {
    $owner = User::factory()->create();
    $collection = Collection::factory()->for($owner)->public()->create();
    CollectionItem::factory()->for($collection)->create(['name' => 'Coffee grinder']);

    $this->actingAs($owner)
        ->get(route('collections.show', $collection))
        ->assertOk()
        ->assertSee('aria-label="Edit Coffee grinder"', false);

    auth()->logout();

    $this->get(route('collections.show', $collection))
        ->assertOk()
        ->assertDontSee('aria-label="Edit Coffee grinder"', false);
});
