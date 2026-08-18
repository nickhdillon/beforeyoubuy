<?php

use App\Livewire\Collections\Index as CollectionsIndex;
use App\Livewire\Collections\Show as ShowCollection;
use App\Models\Collection;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Routing\Route as RouteDefinition;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::bind('collection', function (string $slug, RouteDefinition $route): Collection {
    $user = $route->parameter('user');

    if (is_string($user)) {
        $user = User::query()->where('slug', $user)->firstOrFail();
        $route->setParameter('user', $user);
    }

    if (! $user instanceof User) {
        $user = Auth::user();
        abort_unless($user instanceof User, 404);
    }

    return $user->collections()->where('slug', $slug)->firstOrFail();
});

Route::livewire('{user:slug}/collections/{collection:slug}', ShowCollection::class)
    ->scopeBindings()
    ->name('collections.public');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function (): View {
        $user = Auth::user();
        assert($user instanceof User);

        $collections = $user->collections()
            ->withCount('items')
            ->latest()
            ->get();

        return view('dashboard', [
            'collections' => $collections,
            'itemsCount' => $collections->sum('items_count'),
        ]);
    })->name('dashboard');

    Route::livewire('collections', CollectionsIndex::class)->name('collections.index');
    Route::livewire('collections/{collection:slug}', ShowCollection::class)->name('collections.show');
});

require __DIR__.'/settings.php';
