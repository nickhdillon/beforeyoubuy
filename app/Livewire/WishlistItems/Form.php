<?php

namespace App\Livewire\WishlistItems;

use App\Models\Collection;
use App\Models\Tag;
use App\Models\Wishlist;
use App\Models\WishlistItem;
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

    public Wishlist $wishlist;

    public ?WishlistItem $item = null;

    public ?TemporaryUploadedFile $image = null;

    public string $name = '';

    public string $url = '';

    public string $notes = '';

    public int $quantity = 1;

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

    public function mount(Collection $collection, ?Wishlist $wishlist = null): void
    {
        Gate::authorize('update', $collection);

        $this->collection = $collection;
        $this->wishlist = $wishlist ?? $collection->wishlist()->sole();
    }

    #[On('edit-wishlist-item')]
    public function edit(int $itemId): void
    {
        $item = WishlistItem::query()
            ->whereBelongsTo($this->wishlist)
            ->findOrFail($itemId);

        Gate::authorize('update', $item);

        $this->item = $item;
        $this->image = null;
        $this->name = $item->name ?? '';
        $this->url = $item->url ?? '';
        $this->notes = $item->notes ?? '';
        $this->quantity = $item->quantity;
        $this->rating = $item->rating !== null ? (string) $item->rating : '';
        $this->createAnother = false;
        $this->removeImage = false;
        $this->tagIds = $item->tags()->pluck('tags.id')->all();
        $this->resetValidation();

        Flux::modal('wishlist-item-form')->show();
    }

    public function save(): void
    {
        $validated = $this->validate([
            'image' => [! $this->item || $this->removeImage || ! $this->item->image_path ? 'required' : 'nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'name' => ['nullable', 'string', 'max:120'],
            'url' => ['nullable', 'url:http,https', 'max:2048'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'quantity' => ['required', 'integer', 'min:1', 'max:9999'],
            'rating' => ['nullable', 'numeric', 'in:0.5,1,1.5,2,2.5,3,3.5,4,4.5,5'],
            'tagIds' => ['array'],
            'tagIds.*' => ['integer', Rule::exists(Tag::class, 'id')->where('user_id', $this->collection->user_id)],
        ]);

        if ($this->item instanceof WishlistItem) {
            $this->updateItem($validated);

            return;
        }

        $this->createItem($validated);
    }

    public function delete(): void
    {
        $item = $this->item;
        assert($item instanceof WishlistItem);

        Gate::authorize('delete', $item);

        $imagePath = $item->image_path;

        $item->delete();

        if ($imagePath !== null) {
            Storage::disk('public')->delete($imagePath);
        }

        Flux::modal('delete-wishlist-item')->close();
        Flux::modal('wishlist-item-form')->close();

        $this->resetForm();
        $this->dispatch('wishlist-item-deleted');

        Flux::toast(variant: 'success', text: 'Wishlist item deleted.');
    }

    public function resetForm(): void
    {
        $this->reset(['item', 'image', 'name', 'url', 'notes', 'quantity', 'rating', 'removeImage', 'tagIds']);
        $this->resetValidation();
    }

    public function removePhoto(): void
    {
        Gate::authorize('update', $this->collection);

        $this->image = null;
        $this->removeImage = $this->item instanceof WishlistItem;
        $this->resetValidation('image');
    }

    public function updatedImage(): void
    {
        if ($this->image instanceof TemporaryUploadedFile) {
            $this->removeImage = false;
        }
    }

    public function render(): View
    {
        return view('livewire.wishlist-items.form');
    }

    /**
     * @param  array{image: TemporaryUploadedFile, name: string|null, url: string|null, notes: string|null, quantity: int, rating: numeric-string|null, tagIds: array<int, int>}  $validated
     */
    private function createItem(array $validated): void
    {
        Gate::authorize('create', [WishlistItem::class, $this->wishlist]);

        $image = $this->image;
        assert($image instanceof TemporaryUploadedFile);

        $item = $this->wishlist->items()->create([
            'image_path' => $image->store('wishlist-items', 'public'),
            'name' => filled($validated['name']) ? $validated['name'] : null,
            'url' => filled($validated['url']) ? $validated['url'] : null,
            'notes' => filled($validated['notes']) ? $validated['notes'] : null,
            'quantity' => $validated['quantity'],
            'rating' => filled($validated['rating']) ? $validated['rating'] : null,
        ]);

        $item->tags()->sync($validated['tagIds']);

        $this->reset(['image', 'name', 'url', 'quantity', 'notes', 'rating', 'tagIds']);
        $this->dispatch('wishlist-item-created');

        if ($this->createAnother) {
            Flux::toast(variant: 'success', text: 'Item added. Add another when you’re ready.');

            return;
        }

        Flux::modal('wishlist-item-form')->close();
        Flux::toast(variant: 'success', text: 'Added to wishlist.');
    }

    /**
     * @param  array{image: TemporaryUploadedFile|null, name: string|null, url: string|null, notes: string|null, quantity: int, rating: numeric-string|null, tagIds: array<int, int>}  $validated
     */
    private function updateItem(array $validated): void
    {
        $item = $this->item;
        assert($item instanceof WishlistItem);

        Gate::authorize('update', $item);

        $oldImagePath = $item->image_path;
        $newImagePath = $this->image?->store('wishlist-items', 'public');

        $item->update([
            'image_path' => $newImagePath ?? $oldImagePath,
            'name' => filled($validated['name']) ? $validated['name'] : null,
            'url' => filled($validated['url']) ? $validated['url'] : null,
            'notes' => filled($validated['notes']) ? $validated['notes'] : null,
            'quantity' => $validated['quantity'],
            'rating' => filled($validated['rating']) ? $validated['rating'] : null,
        ]);

        $item->tags()->sync($validated['tagIds']);

        if ($newImagePath !== null && $oldImagePath !== null) {
            Storage::disk('public')->delete($oldImagePath);
        }

        $this->dispatch('wishlist-item-updated');

        Flux::modal('wishlist-item-form')->close();
        Flux::toast(variant: 'success', text: 'Wishlist item updated.');
    }
}
