<?php

declare(strict_types=1);

namespace App\Livewire\Collections;

use App\Models\Collection;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Form extends Component
{
    public ?Collection $collection = null;

    public string $name = '';

    public string $description = '';

    public bool $is_public = false;

    /** @return array<string, list<mixed>> */
    protected function rules(): array
    {
        $uniqueName = Rule::unique(Collection::class)
            ->where('user_id', auth()->id());

        if ($this->collection) {
            $uniqueName->ignore($this->collection);
        }

        return [
            'name' => ['required', 'string', 'max:120', $uniqueName],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_public' => ['boolean'],
        ];
    }

    public function mount(): void
    {
        if ($this->collection) {
            Gate::authorize('update', $this->collection);

            $this->name = $this->collection->name;
            $this->description = $this->collection->description ?? '';
            $this->is_public = $this->collection->is_public;

            return;
        }

        Gate::authorize('create', Collection::class);
    }

    public function save(): void
    {
        $validated = $this->validate();

        if ($this->collection) {
            $this->update($validated);

            return;
        }

        $this->create($validated);
    }

    public function resetForm(): void
    {
        if ($this->collection) {
            $this->collection->refresh();

            $this->name = $this->collection->name;
            $this->description = $this->collection->description ?? '';
            $this->is_public = $this->collection->is_public;
        } else {
            $this->reset(['name', 'description', 'is_public']);
        }

        $this->resetValidation();
    }

    /**
     * @param  array{name: string, description: string|null, is_public: bool}  $validated
     */
    private function create(array $validated): void
    {
        Gate::authorize('create', Collection::class);

        auth()->user()->collections()->create([
            'name' => $validated['name'],
            'description' => filled($validated['description']) ? $validated['description'] : null,
            'is_public' => $validated['is_public'],
        ]);

        $this->reset(['name', 'description', 'is_public']);

        $this->dispatch('collection-created');

        Flux::modal('collection-form')->close();

        Flux::toast(variant: 'success', text: 'Collection created.');
    }

    /**
     * @param  array{name: string, description: string|null, is_public: bool}  $validated
     */
    private function update(array $validated): void
    {
        $collection = $this->collection;

        Gate::authorize('update', $collection);

        $collection->update([
            'name' => $validated['name'],
            'description' => filled($validated['description']) ? $validated['description'] : null,
            'is_public' => $validated['is_public'],
        ]);

        $freshCollection = $collection->fresh();

        $this->collection = $freshCollection;

        $this->dispatch('collection-updated');

        $this->redirectRoute('collections.show', ['collection' => $freshCollection], navigate: true);
    }

    public function delete(): void
    {
        $collection = $this->collection;

        Gate::authorize('delete', $collection);

        $imagePaths = $collection->items()
            ->pluck('image_path')
            ->merge($collection->wishlist->items()->whereNotNull('image_path')->pluck('image_path'));

        $collection->delete();

        Storage::disk('public')->delete($imagePaths->all());

        $this->redirectRoute('collections.index', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.collections.form');
    }
}
