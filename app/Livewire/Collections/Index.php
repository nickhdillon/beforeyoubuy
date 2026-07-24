<?php

namespace App\Livewire\Collections;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Collections')]
class Index extends Component
{
    #[On('collection-created')]
    public function refreshCollections(): void {}

    public function render(): View
    {
        $user = Auth::user();
        assert($user instanceof User);

        return view('livewire.collections.index', [
            'collections' => $user->collections()->with('wishlist')->withCount('items')->latest()->get(),
        ]);
    }
}
