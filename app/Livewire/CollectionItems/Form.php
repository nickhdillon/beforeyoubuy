<?php

namespace App\Livewire\CollectionItems;

use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\Tag;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
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

    public string $url = '';

    public int $quantity = 1;

    public string $notes = '';

    public string $rating = '';

    public bool $createAnother = false;

    public bool $removeImage = false;

    /** @var array<int, int|string> */
    public array $tagIds = [];

    #[Computed]
    public function availableTags(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->collection->user->tags;
    }

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
        $this->url = $item->url ?? '';
        $this->quantity = $item->quantity;
        $this->notes = $item->notes ?? '';
        $this->rating = $item->rating !== null ? (string) $item->rating : '';
        $this->createAnother = false;
        $this->removeImage = false;
        $this->tagIds = $item->tags()->pluck('tags.id')->all();
        $this->resetValidation();

        Flux::modal('collection-item-form')->show();
    }

    public function save(): void
    {
        Gate::authorize('update', $this->collection);

        $validated = $this->validate([
            'image' => [! $this->item || $this->removeImage ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp,heic,heif', 'max:8192'],
            'name' => ['nullable', 'string', 'max:120'],
            'url' => ['nullable', 'url:http,https', 'max:2048'],
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'rating' => ['nullable', 'numeric', 'in:0.5,1,1.5,2,2.5,3,3.5,4,4.5,5'],
            'tagIds' => ['array'],
            'tagIds.*' => ['integer', Rule::exists(Tag::class, 'id')->where('user_id', $this->collection->user_id)],
        ]);

        if ($this->item instanceof CollectionItem) {
            $this->updateItem($validated);

            return;
        }

        $this->createItem($validated);
    }

    public function resetForm(): void
    {
        $this->reset(['item', 'image', 'name', 'url', 'quantity', 'notes', 'rating', 'createAnother', 'removeImage', 'tagIds']);
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
     * @param  array{image: TemporaryUploadedFile, name: string|null, url: string|null, quantity: int, notes: string|null, rating: numeric-string|null, tagIds: array<int, int>}  $validated
     */
    private function createItem(array $validated): void
    {
        $image = $this->image;
        assert($image instanceof TemporaryUploadedFile);

        $item = $this->collection->items()->create([
            'image_path' => $image->store('collection-items', 'public'),
            'name' => filled($validated['name']) ? $validated['name'] : null,
            'url' => filled($validated['url']) ? $validated['url'] : null,
            'quantity' => $validated['quantity'],
            'notes' => filled($validated['notes']) ? $validated['notes'] : null,
            'rating' => filled($validated['rating']) ? $validated['rating'] : null,
        ]);

        $item->tags()->sync($validated['tagIds']);

        $this->reset(['image', 'name', 'url', 'quantity', 'notes', 'rating', 'tagIds']);
        $this->dispatch('collection-item-created');

        if ($this->createAnother) {
            Flux::toast(variant: 'success', text: 'Item added. Add another when you’re ready.');

            return;
        }

        Flux::modal('collection-item-form')->close();
        Flux::toast(variant: 'success', text: 'Item added.');
    }

    /**
     * @param  array{image: TemporaryUploadedFile|null, name: string|null, url: string|null, quantity: int, notes: string|null, rating: numeric-string|null, tagIds: array<int, int>}  $validated
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
            'url' => filled($validated['url']) ? $validated['url'] : null,
            'quantity' => $validated['quantity'],
            'notes' => filled($validated['notes']) ? $validated['notes'] : null,
            'rating' => filled($validated['rating']) ? $validated['rating'] : null,
        ]);

        $item->tags()->sync($validated['tagIds']);

        if ($newImagePath !== null) {
            Storage::disk('public')->delete($oldImagePath);
        }

        $this->dispatch('collection-item-updated');

        Flux::modal('collection-item-form')->close();
        Flux::toast(variant: 'success', text: 'Item updated.');
    }
}
