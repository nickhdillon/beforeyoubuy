<?php

use App\Livewire\Collections\Index as CollectionsIndex;
use App\Livewire\Collections\Show as ShowCollection;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');
Route::livewire('collections/{collection}', ShowCollection::class)->name('collections.show');

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
});

require __DIR__.'/settings.php';
