<?php

use App\Livewire\CollectionItems\Form;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('an owner can edit item details without replacing the photo', function () {
    Storage::fake('public');
    Storage::disk('public')->put('collection-items/original.jpg', 'original');

    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();
    $item = CollectionItem::factory()->for($collection)->create([
        'image_path' => 'collection-items/original.jpg',
        'name' => 'Old name',
        'quantity' => 1,
        'rating' => 3,
    ]);

    $this->actingAs($user);

    Livewire::test(Form::class, ['collection' => $collection])
        ->call('edit', $item->id)
        ->assertSet('name', 'Old name')
        ->assertSet('quantity', 1)
        ->assertSet('rating', '3')
        ->set('name', 'Comandante C40')
        ->set('quantity', 2)
        ->set('rating', '4.5')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('collection-item-updated');

    $item->refresh();

    expect($item->name)->toBe('Comandante C40')
        ->and($item->quantity)->toBe(2)
        ->and($item->rating)->toBe(4.5)
        ->and($item->image_path)->toBe('collection-items/original.jpg');

    Storage::disk('public')->assertExists('collection-items/original.jpg');
});

test('an owner can replace an item photo', function () {
    Storage::fake('public');
    Storage::disk('public')->put('collection-items/original.jpg', 'original');

    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();
    $item = CollectionItem::factory()->for($collection)->create([
        'image_path' => 'collection-items/original.jpg',
    ]);

    $this->actingAs($user);

    Livewire::test(Form::class, ['collection' => $collection])
        ->call('edit', $item->id)
        ->set('image', UploadedFile::fake()->image('replacement.jpg'))
        ->call('save')
        ->assertHasNoErrors();

    $item->refresh();

    expect($item->image_path)->not->toBe('collection-items/original.jpg');
    Storage::disk('public')->assertMissing('collection-items/original.jpg');
    Storage::disk('public')->assertExists($item->image_path);
});

test('an item photo marked for removal must be replaced before saving', function () {
    Storage::fake('public');
    Storage::disk('public')->put('collection-items/original.jpg', 'original');

    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();
    $item = CollectionItem::factory()->for($collection)->create([
        'image_path' => 'collection-items/original.jpg',
    ]);

    $this->actingAs($user);

    $form = Livewire::test(Form::class, ['collection' => $collection])
        ->call('edit', $item->id)
        ->call('removePhoto')
        ->call('save')
        ->assertHasErrors(['image' => 'required']);

    expect($item->fresh()->image_path)->toBe('collection-items/original.jpg');
    Storage::disk('public')->assertExists('collection-items/original.jpg');

    $form->set('image', UploadedFile::fake()->image('replacement.jpg'))
        ->call('save')
        ->assertHasNoErrors();

    $item->refresh();

    expect($item->image_path)->not->toBe('collection-items/original.jpg');
    Storage::disk('public')->assertMissing('collection-items/original.jpg');
    Storage::disk('public')->assertExists($item->image_path);
});

test('the editor cannot select an item from another collection', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();
    $otherItem = CollectionItem::factory()->create();

    $this->actingAs($user);

    expect(fn () => Livewire::test(Form::class, ['collection' => $collection])
        ->call('edit', $otherItem->id))
        ->toThrow(ModelNotFoundException::class);
});

test('an owner can delete an item from its edit form', function () {
    Storage::fake('public');
    Storage::disk('public')->put('collection-items/original.jpg', 'original');

    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();
    $item = CollectionItem::factory()->for($collection)->create([
        'image_path' => 'collection-items/original.jpg',
    ]);

    $this->actingAs($user);

    Livewire::test(Form::class, ['collection' => $collection])
        ->assertDontSee('data-modal="delete-collection-item"', false)
        ->call('edit', $item->id)
        ->assertSee('data-modal="delete-collection-item"', false)
        ->assertDontSee('wire:confirm=', false)
        ->call('delete')
        ->assertSet('item', null)
        ->assertDispatched('collection-item-deleted');

    $this->assertModelMissing($item);
    Storage::disk('public')->assertMissing('collection-items/original.jpg');
});
