<?php

test('the home page includes social sharing metadata', function () {
    $socialImage = asset('social-card.png');

    $this->get(route('home'))
        ->assertSuccessful()
        ->assertSee('<meta property="og:type" content="website">', false)
        ->assertSee('<meta property="og:title" content="Shop smarter - Before You Buy">', false)
        ->assertSee('<meta property="og:image" content="'.$socialImage.'">', false)
        ->assertSee('<meta property="og:image:width" content="1200">', false)
        ->assertSee('<meta property="og:image:height" content="630">', false)
        ->assertSee('<meta name="twitter:card" content="summary_large_image">', false)
        ->assertSee('<meta name="twitter:image" content="'.$socialImage.'">', false);
});

test('the social sharing image is publicly available', function () {
    expect(public_path('social-card.png'))
        ->toBeFile()
        ->and(getimagesize(public_path('social-card.png')))
        ->toMatchArray([1200, 630]);
});
