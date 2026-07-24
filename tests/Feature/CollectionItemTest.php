<?php

use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\User;
use App\Policies\CollectionItemPolicy;

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
    $publicCollection = Collection::factory()->for($owner)->public()->create();
    $privateCollection = Collection::factory()->for($owner)->create();
    $publicItem = CollectionItem::factory()->for($publicCollection)->create();
    $privateItem = CollectionItem::factory()->for($privateCollection)->create();
    $policy = app(CollectionItemPolicy::class);

    expect($policy->view(null, $publicItem))->toBeTrue()
        ->and($policy->view($otherUser, $publicItem))->toBeTrue()
        ->and($policy->view(null, $privateItem))->toBeFalse()
        ->and($policy->update($owner, $publicItem))->toBeTrue()
        ->and($policy->update($otherUser, $publicItem))->toBeFalse();
});
