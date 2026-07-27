<?php

use App\Livewire\WishlistItems\Form;
use App\Models\Collection;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('an owner can edit a wishlist item', function () {
    Storage::fake('public');
    Storage::disk('public')->put('wishlist-items/original.jpg', 'original');
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();
    $item = WishlistItem::factory()->for($collection->wishlist)->create([
        'name' => 'Old grinder',
        'image_path' => 'wishlist-items/original.jpg',
        'quantity' => 1,
    ]);

    $this->actingAs($user);

    Livewire::test(Form::class, ['collection' => $collection])
        ->call('edit', $item->id)
        ->assertSet('name', 'Old grinder')
        ->set('name', 'New grinder')
        ->set('quantity', 2)
        ->set('url', 'https://example.com/new-grinder')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('wishlist-item-updated');

    expect($item->fresh())
        ->name->toBe('New grinder')
        ->quantity->toBe(2)
        ->url->toBe('https://example.com/new-grinder')
        ->image_path->toBe('wishlist-items/original.jpg');
});

test('an owner can replace a wishlist item photo', function () {
    Storage::fake('public');
    Storage::disk('public')->put('wishlist-items/original.jpg', 'original');
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();
    $item = WishlistItem::factory()->for($collection->wishlist)->create([
        'image_path' => 'wishlist-items/original.jpg',
    ]);

    $this->actingAs($user);

    Livewire::test(Form::class, ['collection' => $collection])
        ->call('edit', $item->id)
        ->set('image', UploadedFile::fake()->image('replacement.jpg'))
        ->call('save')
        ->assertHasNoErrors();

    $item->refresh();

    expect($item->image_path)->not->toBe('wishlist-items/original.jpg');
    Storage::disk('public')->assertMissing('wishlist-items/original.jpg');
    Storage::disk('public')->assertExists($item->image_path);
});

test('the editor cannot select an item from another wishlist', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();
    $otherItem = WishlistItem::factory()->create();

    $this->actingAs($user);

    expect(fn () => Livewire::test(Form::class, ['collection' => $collection])
        ->call('edit', $otherItem->id))
        ->toThrow(ModelNotFoundException::class);
});

test('an owner can delete an item from its edit form', function () {
    Storage::fake('public');
    Storage::disk('public')->put('wishlist-items/original.jpg', 'original');
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();
    $item = WishlistItem::factory()->for($collection->wishlist)->create([
        'image_path' => 'wishlist-items/original.jpg',
    ]);

    $this->actingAs($user);

    Livewire::test(Form::class, ['collection' => $collection])
        ->assertDontSee('data-modal="delete-wishlist-item"', false)
        ->call('edit', $item->id)
        ->assertSee('data-modal="delete-wishlist-item"', false)
        ->assertDontSee('wire:confirm=', false)
        ->call('delete')
        ->assertSet('item', null)
        ->assertDispatched('wishlist-item-deleted');

    $this->assertModelMissing($item);
    Storage::disk('public')->assertMissing('wishlist-items/original.jpg');
});
