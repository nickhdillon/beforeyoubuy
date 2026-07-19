<?php

use App\Models\User;

test('the landing page introduces the product to guests', function () {
    $response = $this->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('Shop smarter - Before You Buy')
        ->assertSee('Know what you')
        ->assertSee('before you buy')
        ->assertSee('Keep tabs on your favorite things')
        ->assertSee('Start a collection')
        ->assertSee('Coffee Gear')
        ->assertSee('French press')
        ->assertSee('Pour over')
        ->assertSee('data-brand-mark', escape: false)
        ->assertDontSee('Added last spring')
        ->assertDontSee('Added just now')
        ->assertSee('2 items and counting')
        ->assertSee('Added to collection')
        ->assertSee('share collections with friends and family');
});

test('the authenticated landing page keeps the collections action on one line', function () {
    $response = $this
        ->actingAs(User::factory()->create())
        ->get(route('home'));

    $response
        ->assertOk()
        ->assertSee('View your collections')
        ->assertSee('shrink-0 items-center justify-center gap-3 whitespace-nowrap', escape: false);
});
