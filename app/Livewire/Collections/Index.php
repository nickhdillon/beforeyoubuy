<?php

declare(strict_types=1);

namespace App\Livewire\Collections;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Collections')]
class Index extends Component
{
    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Computed]
    #[On('collection-created')]
    public function collections(): Collection
    {
        return auth()
            ->user()
            ->collections()
            ->with('wishlist')
            ->withCount('items')
            ->when(filled($this->search), fn ($query) => $query->search($this->search))
            ->latest()
            ->get();
    }

    public function render(): View
    {
        return view('livewire.collections.index');
    }
}
