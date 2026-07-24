<?php

use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\User;
use App\Policies\CollectionItemPolicy;
use Illuminate\Support\Facades\DB;

test('an item belongs to a collection with optional details', function () {
    $collection = Collection::factory()->create();
    $item = CollectionItem::factory()->for($collection)->create([
        'name' => null,
        'rating' => null,
    ]);

    expect($item->collection->is($collection))->toBeTrue()
        ->and($item->name)->toBeNull()
        ->and($item->rating)->toBeNull();
});

test('item visibility follows its collection while mutations remain owner only', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $publicCollection = Collection::factory()->for($owner)->public()->create(['name' => 'Public collection']);
    $privateCollection = Collection::factory()->for($owner)->create(['name' => 'Private collection']);
    $publicItem = CollectionItem::factory()->for($publicCollection)->create();
    $privateItem = CollectionItem::factory()->for($privateCollection)->create();
    $policy = app(CollectionItemPolicy::class);

    expect($policy->view(null, $publicItem))->toBeTrue()
        ->and($policy->view($otherUser, $publicItem))->toBeTrue()
        ->and($policy->view(null, $privateItem))->toBeFalse()
        ->and($policy->update($owner, $publicItem))->toBeTrue()
        ->and($policy->update($otherUser, $publicItem))->toBeFalse();
});

test('item policy checks reuse the collection already loaded by the page', function () {
    $owner = User::factory()->create();
    $collection = Collection::factory()->for($owner)->public()->create();
    CollectionItem::factory()->count(3)->for($collection)->create();

    $loadedCollection = $collection->fresh()->load(['user', 'items']);
    $policy = app(CollectionItemPolicy::class);

    foreach ($loadedCollection->items as $item) {
        expect($item->relationLoaded('collection'))->toBeTrue()
            ->and($item->collection)->toBe($loadedCollection);
    }

    DB::enableQueryLog();

    foreach ($loadedCollection->items as $item) {
        expect($policy->update($owner, $item))->toBeTrue();
    }

    $queries = DB::getQueryLog();
    DB::disableQueryLog();

    expect($queries)->toBeEmpty();
});
