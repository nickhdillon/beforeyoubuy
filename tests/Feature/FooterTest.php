<?php

use App\Models\User;

test('the footer is shown to guests on public and authentication pages', function (string $route) {
    $this->get(route($route))
        ->assertSuccessful()
        ->assertSee('data-test="app-footer"', false)
        ->assertSee('Before You Buy')
        ->assertSee((string) now()->year)
        ->assertSee('Log in');
})->with([
    'home' => 'home',
    'login' => 'login',
]);

test('the footer is shown on authenticated pages', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertSee('data-test="app-footer"', false)
        ->assertSee('Dashboard')
        ->assertDontSee('>Log in</a>', false);
});
