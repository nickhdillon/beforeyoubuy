<?php

namespace App\Livewire\Collections;

use App\Models\Collection;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.public')]
#[Title('Collection')]
class Show extends Component
{
    public Collection $collection;

    public function mount(Collection $collection): void
    {
        Gate::authorize('view', $collection);

        $this->collection = $collection->load(['user', 'items']);
    }

    #[On('collection-item-created')]
    public function refreshItems(): void
    {
        $this->collection->load('items');
    }

    #[On('collection-item-updated')]
    public function refreshUpdatedItems(): void
    {
        $this->refreshItems();
    }

    #[On('collection-updated')]
    public function refreshCollection(): void
    {
        $this->collection->refresh()->load(['user', 'items']);
    }

    public function render(): View
    {
        return view('livewire.collections.show');
    }
}
