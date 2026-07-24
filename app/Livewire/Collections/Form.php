<?php

namespace App\Livewire\Collections;

use App\Models\Collection;
use App\Models\User;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class Form extends Component
{
    public ?Collection $collection = null;

    public string $name = '';

    public string $description = '';

    public bool $isPublic = false;

    public function mount(?Collection $collection = null): void
    {
        if ($collection instanceof Collection) {
            Gate::authorize('update', $collection);

            $this->collection = $collection;
            $this->fillFromCollection();

            return;
        }

        Gate::authorize('create', Collection::class);
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'isPublic' => ['boolean'],
        ]);

        if ($this->collection instanceof Collection) {
            $this->updateCollection($validated);

            return;
        }

        $this->createCollection($validated);
    }

    public function resetForm(): void
    {
        if ($this->collection instanceof Collection) {
            $this->collection->refresh();
            $this->fillFromCollection();
        } else {
            $this->reset(['name', 'description', 'isPublic']);
        }

        $this->resetValidation();
    }

    public function render(): View
    {
        return view('livewire.collections.form');
    }

    /**
     * @param  array{name: string, description: string|null, isPublic: bool}  $validated
     */
    private function createCollection(array $validated): void
    {
        Gate::authorize('create', Collection::class);

        $user = Auth::user();
        assert($user instanceof User);

        DB::transaction(function () use ($user, $validated): void {
            $collection = $user->collections()->create([
                'name' => $validated['name'],
                'description' => filled($validated['description']) ? $validated['description'] : null,
                'is_public' => $validated['isPublic'],
            ]);

            $collection->wishlist()->create();
        });

        $this->reset(['name', 'description', 'isPublic']);
        $this->dispatch('collection-created');

        Flux::modal('collection-form')->close();
        Flux::toast(variant: 'success', text: 'Collection created.');
    }

    /**
     * @param  array{name: string, description: string|null, isPublic: bool}  $validated
     */
    private function updateCollection(array $validated): void
    {
        $collection = $this->collection;
        assert($collection instanceof Collection);

        Gate::authorize('update', $collection);

        $collection->update([
            'name' => $validated['name'],
            'description' => filled($validated['description']) ? $validated['description'] : null,
            'is_public' => $validated['isPublic'],
        ]);

        $freshCollection = $collection->fresh();
        assert($freshCollection instanceof Collection);

        $this->collection = $freshCollection;
        $this->dispatch('collection-updated');

        $this->redirectRoute('collections.show', ['collection' => $freshCollection], navigate: true);
    }

    private function fillFromCollection(): void
    {
        $collection = $this->collection;
        assert($collection instanceof Collection);

        $this->name = $collection->name;
        $this->description = $collection->description ?? '';
        $this->isPublic = $collection->is_public;
    }
}
