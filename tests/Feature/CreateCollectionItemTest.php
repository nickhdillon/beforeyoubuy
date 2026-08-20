<?php

use App\Livewire\CollectionItems\Form;
use App\Livewire\CollectionItems\Index;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\Tag;
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
        ->set('url', 'https://example.com/kettle')
        ->set('notes', 'Daily driver.')
        ->call('save')
        ->assertHasNoErrors();

    $item = CollectionItem::query()->sole();

    expect($item->name)->toBe('Gooseneck kettle')
        ->and($item->quantity)->toBe(2)
        ->and($item->rating)->toBe(4.5)
        ->and($item->url)->toBe('https://example.com/kettle')
        ->and($item->notes)->toBe('Daily driver.');
});

test('an item link must use HTTP or HTTPS', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();

    $this->actingAs($user);

    Livewire::test(Form::class, ['collection' => $collection])
        ->set('image', UploadedFile::fake()->image('kettle.jpg'))
        ->set('url', 'javascript:alert(1)')
        ->call('save')
        ->assertHasErrors(['url']);
});

test('an owner can create items back to back', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();
    $tag = Tag::factory()->for($user)->create();

    $this->actingAs($user);

    Livewire::test(Form::class, ['collection' => $collection])
        ->assertSeeHtml('wire:model.live="createAnother"')
        ->set('createAnother', true)
        ->set('tagIds', [$tag->id])
        ->set('image', UploadedFile::fake()->image('grinder.jpg'))
        ->set('name', 'Hand grinder')
        ->call('save')
        ->assertHasNoErrors()
        ->assertNotDispatched('collection-item-created')
        ->assertNotDispatched('modal-close')
        ->assertSet('createAnother', true)
        ->assertSet('tagIds', [$tag->id])
        ->assertSet('hasCreatedItemsAwaitingRefresh', true)
        ->assertSet('image', null)
        ->assertSet('name', '')
        ->set('image', UploadedFile::fake()->image('kettle.jpg'))
        ->set('name', 'Kettle')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('tagIds', [$tag->id])
        ->call('resetForm')
        ->assertDispatched('collection-item-created');

    expect($collection->items()->pluck('name')->all())->toBe(['Hand grinder', 'Kettle'])
        ->and($collection->items()->with('tags')->get()->every(fn (CollectionItem $item): bool => $item->tags->contains($tag)))->toBeTrue();
});

test('collection items use the compact uncropped mobile card layout without fallback names', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();
    CollectionItem::factory()->for($collection)->create([
        'name' => null,
        'image_path' => 'collection-items/portrait.jpg',
    ]);

    Livewire::actingAs($user)
        ->test(Index::class, ['collection' => $collection])
        ->assertSeeHtml('grid grid-cols-2 gap-5 lg:grid-cols-3')
        ->assertSeeHtml('hard-shadow m-3 mb-0 overflow-hidden border-2 border-zinc-950 bg-emerald-50')
        ->assertSeeHtml('class="aspect-square w-full object-cover transition duration-300 group-hover:saturate-110"')
        ->assertDontSee('Untitled item')
        ->assertDontSee('untitled item');
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
