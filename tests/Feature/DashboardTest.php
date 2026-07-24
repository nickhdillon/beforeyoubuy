<?php

use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk()
        ->assertSee('New collection')
        ->assertSee('Give your things a home');
});

test('the dashboard displays the users collections and item totals', function () {
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create([
        'name' => 'Coffee gear',
    ]);
    CollectionItem::factory()->count(2)->for($collection)->create();
    Collection::factory()->create([
        'name' => 'Someone else’s collection',
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('Coffee gear')
        ->assertSee('2 items')
        ->assertSee('href="'.route('collections.show', $collection).'"', false)
        ->assertDontSee('Someone else’s collection');
});
