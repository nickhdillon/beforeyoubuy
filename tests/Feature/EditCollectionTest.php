<?php

use App\Livewire\Collections\Form;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('an owner can edit a collection', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create([
        'name' => 'Old name',
        'description' => 'Old description.',
        'is_public' => false,
    ]);

    $this->actingAs($user);

    $component = Livewire::test(Form::class, ['collection' => $collection])
        ->assertSet('name', 'Old name')
        ->assertSet('description', 'Old description.')
        ->assertSet('isPublic', false)
        ->set('name', 'Coffee favorites')
        ->set('description', 'The gear I use every day.')
        ->set('isPublic', true)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('collection-updated');

    $collection->refresh();

    expect($collection->name)->toBe('Coffee favorites')
        ->and($collection->slug)->toBe('coffee-favorites')
        ->and($collection->description)->toBe('The gear I use every day.')
        ->and($collection->is_public)->toBeTrue();

    $component->assertRedirect(route('collections.show', $collection));
});

test('a collection name is still required when editing', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();

    $this->actingAs($user);

    Livewire::test(Form::class, ['collection' => $collection])
        ->set('name', '')
        ->call('save')
        ->assertHasErrors(['name' => 'required']);
});

test('another user cannot open the collection editor', function () {
    $collection = Collection::factory()->create();

    $this->actingAs(User::factory()->create());

    Livewire::test(Form::class, ['collection' => $collection])
        ->assertForbidden();
});

test('an owner can delete a collection from its edit form', function () {
    Storage::fake('public');
    Storage::disk('public')->put('collection-items/first.jpg', 'first');
    Storage::disk('public')->put('collection-items/second.jpg', 'second');

    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();
    $items = CollectionItem::factory()->for($collection)->createMany([
        ['image_path' => 'collection-items/first.jpg'],
        ['image_path' => 'collection-items/second.jpg'],
    ]);

    $this->actingAs($user);

    Livewire::test(Form::class, ['collection' => $collection])
        ->assertSee('data-modal="delete-collection"', false)
        ->assertDontSee('wire:confirm=', false)
        ->call('delete')
        ->assertRedirect(route('collections.index'));

    $this->assertModelMissing($collection);

    foreach ($items as $item) {
        $this->assertModelMissing($item);
    }

    Storage::disk('public')->assertMissing([
        'collection-items/first.jpg',
        'collection-items/second.jpg',
    ]);
});

test('the create collection form does not offer deletion', function () {
    $this->actingAs(User::factory()->create());

    Livewire::test(Form::class)
        ->assertDontSee('data-modal="delete-collection"', false);
});
