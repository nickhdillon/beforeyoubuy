<?php

namespace App\Livewire\CollectionItems;

use App\Models\Collection;
use App\Models\CollectionItem;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class Form extends Component
{
    use WithFileUploads;

    public Collection $collection;

    public ?CollectionItem $item = null;

    public ?TemporaryUploadedFile $image = null;

    public string $name = '';

    public int $quantity = 1;

    public string $notes = '';

    public string $rating = '';

    public bool $createAnother = false;

    public bool $removeImage = false;

    public function mount(Collection $collection): void
    {
        Gate::authorize('update', $collection);

        $this->collection = $collection;
    }

    #[On('edit-collection-item')]
    public function edit(int $itemId): void
    {
        $item = CollectionItem::query()
            ->whereBelongsTo($this->collection)
            ->findOrFail($itemId);

        Gate::authorize('update', $item);

        $this->item = $item;
        $this->image = null;
        $this->name = $item->name ?? '';
        $this->quantity = $item->quantity;
        $this->notes = $item->notes ?? '';
        $this->rating = $item->rating !== null ? (string) $item->rating : '';
        $this->createAnother = false;
        $this->removeImage = false;
        $this->resetValidation();

        Flux::modal('collection-item-form')->show();
    }

    public function save(): void
    {
        Gate::authorize('update', $this->collection);

        $validated = $this->validate([
            'image' => [! $this->item || $this->removeImage ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'name' => ['nullable', 'string', 'max:120'],
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'rating' => ['nullable', 'numeric', 'in:0.5,1,1.5,2,2.5,3,3.5,4,4.5,5'],
        ]);

        if ($this->item instanceof CollectionItem) {
            $this->updateItem($validated);

            return;
        }

        $this->createItem($validated);
    }

    public function resetForm(): void
    {
        $this->reset(['item', 'image', 'name', 'quantity', 'notes', 'rating', 'createAnother', 'removeImage']);
        $this->resetValidation();
    }

    public function removePhoto(): void
    {
        Gate::authorize('update', $this->collection);

        $this->image = null;
        $this->removeImage = $this->item instanceof CollectionItem;
        $this->resetValidation('image');
    }

    public function updatedImage(): void
    {
        if ($this->image instanceof TemporaryUploadedFile) {
            $this->removeImage = false;
        }
    }

    public function delete(): void
    {
        $item = $this->item;
        assert($item instanceof CollectionItem);

        Gate::authorize('delete', $item);

        $imagePath = $item->image_path;

        $item->delete();
        Storage::disk('public')->delete($imagePath);

        Flux::modal('delete-collection-item')->close();
        Flux::modal('collection-item-form')->close();

        $this->resetForm();
        $this->dispatch('collection-item-deleted');

        Flux::toast(variant: 'success', text: 'Item deleted.');
    }

    public function render(): View
    {
        return view('livewire.collection-items.form');
    }

    /**
     * @param  array{image: TemporaryUploadedFile, name: string|null, quantity: int, notes: string|null, rating: numeric-string|null}  $validated
     */
    private function createItem(array $validated): void
    {
        $image = $this->image;
        assert($image instanceof TemporaryUploadedFile);

        $this->collection->items()->create([
            'image_path' => $image->store('collection-items', 'public'),
            'name' => filled($validated['name']) ? $validated['name'] : null,
            'quantity' => $validated['quantity'],
            'notes' => filled($validated['notes']) ? $validated['notes'] : null,
            'rating' => filled($validated['rating']) ? $validated['rating'] : null,
        ]);

        $this->reset(['image', 'name', 'quantity', 'notes', 'rating']);
        $this->dispatch('collection-item-created');

        if ($this->createAnother) {
            Flux::toast(variant: 'success', text: 'Item added. Add another when you’re ready.');

            return;
        }

        Flux::modal('collection-item-form')->close();
        Flux::toast(variant: 'success', text: 'Item added.');
    }

    /**
     * @param  array{image: TemporaryUploadedFile|null, name: string|null, quantity: int, notes: string|null, rating: numeric-string|null}  $validated
     */
    private function updateItem(array $validated): void
    {
        $item = $this->item;
        assert($item instanceof CollectionItem);

        Gate::authorize('update', $item);

        $oldImagePath = $item->image_path;
        $newImagePath = $this->image?->store('collection-items', 'public');

        $item->update([
            'image_path' => $newImagePath ?? $oldImagePath,
            'name' => filled($validated['name']) ? $validated['name'] : null,
            'quantity' => $validated['quantity'],
            'notes' => filled($validated['notes']) ? $validated['notes'] : null,
            'rating' => filled($validated['rating']) ? $validated['rating'] : null,
        ]);

        if ($newImagePath !== null) {
            Storage::disk('public')->delete($oldImagePath);
        }

        $this->dispatch('collection-item-updated');

        Flux::modal('collection-item-form')->close();
        Flux::toast(variant: 'success', text: 'Item updated.');
    }
}
