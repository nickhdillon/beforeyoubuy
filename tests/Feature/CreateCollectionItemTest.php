<?php

use App\Livewire\CollectionItems\Form;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('an owner can quick add an item with only a photo', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();
    $image = UploadedFile::fake()->image('grinder.jpg', 1200, 1200);

    $this->actingAs($user);

    Livewire::test(Form::class, ['collection' => $collection])
        ->set('image', $image)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('collection-item-created');

    $item = CollectionItem::query()->sole();

    expect($item->collection->is($collection))->toBeTrue()
        ->and($item->name)->toBeNull()
        ->and($item->rating)->toBeNull();

    Storage::disk('public')->assertExists($item->image_path);
});

test('an item photo is required', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();

    $this->actingAs($user);

    Livewire::test(Form::class, ['collection' => $collection])
        ->set('image', UploadedFile::fake()->image('temporary.jpg'))
        ->call('removePhoto')
        ->assertSet('image', null)
        ->call('save')
        ->assertHasErrors(['image' => 'required']);
});

test('optional item details can be saved', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();

    $this->actingAs($user);

    Livewire::test(Form::class, ['collection' => $collection])
        ->set('image', UploadedFile::fake()->image('kettle.jpg'))
        ->set('name', 'Gooseneck kettle')
        ->set('quantity', 2)
        ->set('rating', '4.5')
        ->set('notes', 'Daily driver.')
        ->call('save')
        ->assertHasNoErrors();

    $item = CollectionItem::query()->sole();

    expect($item->name)->toBe('Gooseneck kettle')
        ->and($item->quantity)->toBe(2)
        ->and($item->rating)->toBe(4.5)
        ->and($item->notes)->toBe('Daily driver.');
});

test('an owner can create items back to back', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();

    $this->actingAs($user);

    Livewire::test(Form::class, ['collection' => $collection])
        ->set('createAnother', true)
        ->set('image', UploadedFile::fake()->image('grinder.jpg'))
        ->set('name', 'Hand grinder')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('createAnother', true)
        ->assertSet('image', null)
        ->assertSet('name', '')
        ->set('image', UploadedFile::fake()->image('kettle.jpg'))
        ->set('name', 'Kettle')
        ->call('save')
        ->assertHasNoErrors();

    expect($collection->items()->pluck('name')->all())->toBe(['Hand grinder', 'Kettle']);
});

test('ratings must use half-star increments', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();

    $this->actingAs($user);

    Livewire::test(Form::class, ['collection' => $collection])
        ->set('image', UploadedFile::fake()->image('kettle.jpg'))
        ->set('rating', '4.25')
        ->call('save')
        ->assertHasErrors(['rating']);
});
