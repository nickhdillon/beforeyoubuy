<?php

use App\Livewire\WishlistItems\Form;
use App\Models\Collection;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

test('an owner can add an item to a collection wishlist', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();

    $this->actingAs($user);

    Livewire::test(Form::class, ['collection' => $collection])
        ->set('image', UploadedFile::fake()->image('grinder.jpg'))
        ->set('name', 'Comandante C40')
        ->set('url', 'https://example.com/grinder')
        ->set('quantity', 2)
        ->set('rating', '4.5')
        ->set('notes', 'Black finish.')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('wishlist-item-created');

    $item = WishlistItem::query()->sole();

    expect($item->wishlist->is($collection->wishlist))->toBeTrue()
        ->and($item->name)->toBe('Comandante C40')
        ->and($item->url)->toBe('https://example.com/grinder')
        ->and($item->quantity)->toBe(2)
        ->and($item->rating)->toBe(4.5)
        ->and($item->notes)->toBe('Black finish.');

    Storage::disk('public')->assertExists($item->image_path);
});

test('wishlist item fields are validated', function (string $property, mixed $value, string $rule) {
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();

    $this->actingAs($user);

    Livewire::test(Form::class, ['collection' => $collection])
        ->set('image', UploadedFile::fake()->image('grinder.jpg'))
        ->set($property, $value)
        ->call('save')
        ->assertHasErrors([$property => $rule]);
})->with([
    'name length' => ['name', str_repeat('a', 121), 'max'],
    'link must be a URL' => ['url', 'not-a-url', 'url'],
    'link must use HTTP' => ['url', 'javascript:alert(1)', 'url'],
    'quantity minimum' => ['quantity', 0, 'min'],
]);

test('a wishlist item photo is required', function () {
    Storage::fake('public');
    $user = User::factory()->create();
    $collection = Collection::factory()->for($user)->create();

    $this->actingAs($user);

    Livewire::test(Form::class, ['collection' => $collection])
        ->call('save')
        ->assertHasErrors(['image' => 'required']);
});

test('another user cannot mount the wishlist form', function () {
    $collection = Collection::factory()->create();

    $this->actingAs(User::factory()->create());

    Livewire::test(Form::class, ['collection' => $collection])
        ->assertNotFound();
});
