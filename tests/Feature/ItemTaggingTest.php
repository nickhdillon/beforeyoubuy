<?php

use App\Actions\MoveCollectionItem;
use App\Livewire\CollectionItems\Form as CollectionItemForm;
use App\Livewire\WishlistItems\Form as WishlistItemForm;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\Tag;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('collection items can have multiple tags', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();
    $tags = Tag::factory()->count(2)->for($user)->create();

    Livewire::actingAs($user)
        ->test(CollectionItemForm::class, ['collection' => $collection])
        ->assertSee('Choose tags…')
        ->assertSeeHtml('class="text-xs font-black text-emerald-700 hover:text-emerald-900">Add tag</button>')
        ->set('image', UploadedFile::fake()->image('item.jpg'))
        ->set('name', 'Kettle')
        ->set('tagIds', $tags->modelKeys())
        ->assertSee($tags->first()->name)
        ->assertSee($tags->last()->name)
        ->call('save')
        ->assertHasNoErrors();

    expect($collection->items()->sole()->tags()->pluck('tags.id')->all())
        ->toEqualCanonicalizing($tags->modelKeys());
});

test('wishlist item tags can be updated', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();
    $tags = Tag::factory()->count(2)->for($user)->create();
    $item = WishlistItem::factory()->for($collection->wishlist)->create();
    $item->tags()->attach($tags->first());

    Livewire::actingAs($user)
        ->test(WishlistItemForm::class, ['collection' => $collection])
        ->call('edit', $item->id)
        ->assertSet('tagIds', [$tags->first()->id])
        ->set('tagIds', [$tags->last()->id])
        ->call('save')
        ->assertHasNoErrors();

    expect($item->tags()->pluck('tags.id')->all())->toBe([$tags->last()->id]);
});

test('a tag can be created and selected without resetting the collection item form', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(CollectionItemForm::class, ['collection' => $collection])
        ->assertSee('Create tag')
        ->set('name', 'Travel kettle')
        ->set('quantity', 2)
        ->set('tagForm.name', '  Travel   gear  ')
        ->call('createTag')
        ->assertHasNoErrors()
        ->assertSet('name', 'Travel kettle')
        ->assertSet('quantity', 2)
        ->assertSet('tagForm.name', '')
        ->assertSet('tagIds', [$user->tags()->sole()->id])
        ->assertSee('Travel gear');

    expect($user->tags()->sole()->name)->toBe('Travel gear');
});

test('a tag can be created and selected without resetting the wishlist item form', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();

    Livewire::actingAs($user)
        ->test(WishlistItemForm::class, ['collection' => $collection])
        ->assertSee('Create tag')
        ->set('name', 'Camera')
        ->set('url', 'https://example.com/camera')
        ->set('tagForm.name', 'Photography')
        ->call('createTag')
        ->assertHasNoErrors()
        ->assertSet('name', 'Camera')
        ->assertSet('url', 'https://example.com/camera')
        ->assertSet('tagForm.name', '')
        ->assertSet('tagIds', [$user->tags()->sole()->id])
        ->assertSee('Photography');
});

test('inline tag names must be unique for the collection owner', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();
    Tag::factory()->for($user)->create(['name' => 'Favorite']);

    Livewire::actingAs($user)
        ->test(CollectionItemForm::class, ['collection' => $collection])
        ->set('name', 'Preserved item name')
        ->set('tagForm.name', 'Favorite')
        ->call('createTag')
        ->assertHasErrors(['tagForm.name' => 'unique'])
        ->assertSet('name', 'Preserved item name')
        ->assertSet('tagIds', []);
});

test('an item cannot use another users tag', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();
    $otherTag = Tag::factory()->for(User::factory())->create();

    Livewire::actingAs($user)
        ->test(CollectionItemForm::class, ['collection' => $collection])
        ->set('image', UploadedFile::fake()->image('item.jpg'))
        ->set('tagIds', [$otherTag->id])
        ->call('save')
        ->assertHasErrors(['tagIds.0' => 'exists']);
});

test('tags are preserved when moving an item', function () {
    Storage::fake('public');
    Storage::disk('public')->put('collection-items/item.jpg', 'image');
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();
    $tag = Tag::factory()->for($user)->create();
    $item = CollectionItem::factory()->for($collection)->create(['image_path' => 'collection-items/item.jpg']);
    $item->tags()->attach($tag);

    $wishlistItem = app(MoveCollectionItem::class)->toWishlist($item, $collection->wishlist);

    expect($wishlistItem->tags()->sole()->is($tag))->toBeTrue();
});

test('tags are always returned alphabetically for items and users', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();
    $collectionItem = CollectionItem::factory()->for($collection)->create();
    $wishlistItem = WishlistItem::factory()->for($collection->wishlist)->create();
    $tags = collect(['Zebra', 'Alpha', 'Middle'])
        ->map(fn (string $name): Tag => Tag::factory()->for($user)->create(['name' => $name]));

    $collectionItem->tags()->attach($tags);
    $wishlistItem->tags()->attach($tags);

    expect($user->tags->pluck('name')->all())->toBe(['Alpha', 'Middle', 'Zebra'])
        ->and($collectionItem->tags->pluck('name')->all())->toBe(['Alpha', 'Middle', 'Zebra'])
        ->and($wishlistItem->tags->pluck('name')->all())->toBe(['Alpha', 'Middle', 'Zebra']);
});
