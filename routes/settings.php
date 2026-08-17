<?php

use App\Livewire\Settings\Profile;
use App\Livewire\Settings\Security;
use App\Livewire\Settings\Tags;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::livewire('settings/profile', Profile::class)->name('profile.edit');
    Route::livewire('settings/tags', Tags::class)->name('tags.edit');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('settings/security', Security::class)
        ->middleware([
            'password.confirm',
        ])
        ->name('security.edit');
});
