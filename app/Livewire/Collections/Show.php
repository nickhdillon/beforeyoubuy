<?php

declare(strict_types=1);

namespace App\Livewire\Collections;

use App\Models\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

/** @property-read Carbon $lastUpdatedAt */
#[Layout('layouts.public')]
#[Title('Collection')]
class Show extends Component
{
    public Collection $collection;

    public function mount(): void
    {
        Gate::authorize('view', $this->collection);
        $this->collection->load('user');
    }

    #[Computed]
    #[On('collection-item-created')]
    #[On('collection-item-updated')]
    #[On('collection-item-deleted')]
    public function lastUpdatedAt(): Carbon
    {
        return $this->collection->last_updated_at;
    }

    #[On('collection-updated')]
    public function refreshCollection(): void
    {
        $this->collection->refresh()->load('user');
        unset($this->lastUpdatedAt);
    }
}
