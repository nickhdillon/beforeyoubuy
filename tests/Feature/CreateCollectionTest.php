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
        ->set('is_public', true)
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

test('a user cannot create collections with duplicate names', function () {
    $user = User::factory()->create();

    Collection::factory()->for($user)->create(['name' => 'Espresso Setup']);

    $this->actingAs($user);

    Livewire::test(Form::class)
        ->set('name', 'Espresso Setup')
        ->call('save')
        ->assertHasErrors(['name' => 'unique'])
        ->assertNotDispatched('collection-created');

    expect(Collection::query()->count())->toBe(1);
});

test('different users can use the same collection name', function () {
    Collection::factory()->create(['name' => 'Espresso Setup']);
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Form::class)
        ->set('name', 'Espresso Setup')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('collection-created');

    expect(Collection::query()->orderBy('id')->pluck('slug')->all())->toBe([
        'espresso-setup',
        'espresso-setup',
    ]);
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
