<?php

use App\Livewire\Collections\Form;
use App\Models\Collection;
use App\Models\User;
use Livewire\Livewire;

test('a user can create a collection with its private wishlist', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Form::class)
        ->set('name', 'Coffee Gear')
        ->set('description', 'My daily brewing setup.')
        ->set('isPublic', true)
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('collection-created');

    $collection = Collection::query()->sole();

    expect($collection->user->is($user))->toBeTrue()
        ->and($collection->name)->toBe('Coffee Gear')
        ->and($collection->description)->toBe('My daily brewing setup.')
        ->and($collection->is_public)->toBeTrue()
        ->and($collection->wishlist)->not->toBeNull();
});

test('collection fields are validated', function (string $property, mixed $value, string $rule) {
    $this->actingAs(User::factory()->create());

    Livewire::test(Form::class)
        ->set($property, $value)
        ->call('save')
        ->assertHasErrors([$property => $rule]);
})->with([
    'name is required' => ['name', '', 'required'],
    'name length' => ['name', str_repeat('a', 121), 'max'],
]);
