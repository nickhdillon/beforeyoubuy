<?php

use App\Livewire\Settings\Tags;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Livewire\Livewire;

test('a user can create and rename their tags', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Tags::class)
        ->set('name', 'Coffee gear')
        ->call('create')
        ->assertHasNoErrors()
        ->assertDispatched('tag-created');

    $tag = $user->tags()->sole();

    Livewire::actingAs($user)
        ->test(Tags::class)
        ->call('edit', $tag->id)
        ->set('editingName', 'Brewing gear')
        ->call('update')
        ->assertHasNoErrors();

    expect($tag->fresh()->name)->toBe('Brewing gear');
});

test('tag names must be unique per user', function () {
    $user = User::factory()->create();
    Tag::factory()->for($user)->create(['name' => 'Favorite']);

    Livewire::actingAs($user)
        ->test(Tags::class)
        ->set('name', 'Favorite')
        ->call('create')
        ->assertHasErrors(['name' => 'unique']);
});

test('a user cannot modify another users tag', function () {
    $user = User::factory()->create();
    $otherTag = Tag::factory()->for(User::factory())->create();

    expect(fn () => Livewire::actingAs($user)
        ->test(Tags::class)
        ->call('delete', $otherTag->id))
        ->toThrow(ModelNotFoundException::class);

    $this->assertModelExists($otherTag);
});
