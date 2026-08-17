<?php

declare(strict_types=1);

namespace App\Livewire\Collections;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Collections')]
class Index extends Component
{
    #[Computed]
    #[On('collection-created')]
    public function collections(): Collection
    {
        return auth()
            ->user()
            ->collections()
            ->with('wishlist')
            ->withCount('items')
            ->latest()
            ->get();
    }

    public function render(): View
    {
        return view('livewire.collections.index');
    }
}
