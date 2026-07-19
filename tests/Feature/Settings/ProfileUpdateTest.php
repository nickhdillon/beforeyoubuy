<?php

use App\Livewire\Settings\Profile;
use App\Models\User;
use Livewire\Livewire;

test('profile page is displayed', function () {
    $this->actingAs($user = User::factory()->create());

    $response = $this->get('/settings/profile');

    $response
        ->assertOk()
        ->assertSee('Delete account')
        ->assertSee('data-test="delete-account-trigger"', escape: false);

    preg_match('/<button[^>]*data-test="delete-account-trigger"[^>]*>/', $response->getContent(), $deleteAccountButton);
    preg_match('/<button[^>]*data-test="password-visibility-toggle"[^>]*>/', $response->getContent(), $passwordVisibilityButton);
    preg_match('/<dialog[^>]*data-modal="confirm-user-deletion"[^>]*>/', $response->getContent(), $deleteAccountModal);

    expect($deleteAccountButton[0] ?? '')
        ->toContain('border-2 border-zinc-950')
        ->toContain('shadow-[5px_6px_0]')
        ->toContain('hover:-translate-y-1')
        ->toContain('hover:shadow-[7px_9px_0]')
        ->toContain('active:shadow-[2px_3px_0]');

    expect($passwordVisibilityButton[0] ?? '')
        ->toContain('border-0')
        ->toContain('shadow-none')
        ->toContain('hover:translate-y-0')
        ->not->toContain('shadow-[5px_6px_0]');

    expect($deleteAccountModal[0] ?? '')
        ->toContain('rounded-none')
        ->toContain('border-[3px] border-zinc-950')
        ->toContain('shadow-none')
        ->toContain('sm:shadow-[10px_12px_0]')
        ->toContain('sm:shadow-zinc-950')
        ->toContain('backdrop:bg-zinc-950/55');
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test(Profile::class)
        ->set('name', 'Test User')
        ->set('email', 'test@example.com')
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toEqual('Test User');
    expect($user->email)->toEqual('test@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('email verification status is unchanged when email address is unchanged', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test(Profile::class)
        ->set('name', 'Test User')
        ->set('email', $user->email)
        ->call('updateProfileInformation');

    $response->assertHasNoErrors();

    expect($user->refresh()->email_verified_at)->not->toBeNull();
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('settings.delete-user-form')
        ->set('password', 'password')
        ->call('deleteUser');

    $response
        ->assertHasNoErrors()
        ->assertRedirect('/');

    expect($user->fresh())->toBeNull();
    expect(auth()->check())->toBeFalse();
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Livewire::test('settings.delete-user-form')
        ->set('password', 'wrong-password')
        ->call('deleteUser');

    $response->assertHasErrors(['password']);

    expect($user->fresh())->not->toBeNull();
});
