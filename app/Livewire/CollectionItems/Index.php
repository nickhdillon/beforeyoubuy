<?php

declare(strict_types=1);

namespace App\Livewire\CollectionItems;

use App\Actions\MoveCollectionItem;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\WishlistItem;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class Index extends Component
{
    public Collection $collection;

    public ?CollectionItem $pendingItem = null;

    public function mount(): void
    {
        Gate::authorize('view', $this->collection);
    }

    #[Computed]
    #[On('collection-item-created')]
    #[On('collection-item-updated')]
    #[On('collection-item-deleted')]
    public function items(): EloquentCollection
    {
        return $this->collection->items()->with('tags')->get();
    }

    public function confirmMoveToWishlist(int $itemId): void
    {
        $item = $this->findItem($itemId);

        Gate::authorize('update', $item);

        $this->pendingItem = $item;

        Flux::modal('move-item-to-wishlist')->show();
    }

    public function moveToWishlist(MoveCollectionItem $moveItem): void
    {
        if ($this->pendingItem === null) {
            return;
        }

        Gate::authorize('update', $this->pendingItem);

        $wishlist = $this->collection->wishlist()->sole();

        Gate::authorize('create', [WishlistItem::class, $wishlist]);

        $moveItem->toWishlist($this->pendingItem, $wishlist);

        Flux::modal('move-item-to-wishlist')->close();

        $this->pendingItem = null;

        unset($this->items);

        $this->dispatch('collection-item-deleted');
        $this->dispatch('wishlist-item-created');

        Flux::toast(variant: 'success', text: 'Item moved to wishlist.');
    }

    public function confirmDelete(int $itemId): void
    {
        $item = $this->findItem($itemId);

        Gate::authorize('delete', $item);

        $this->pendingItem = $item;

        Flux::modal('delete-collection-item-from-card')->show();
    }

    public function delete(): void
    {
        if ($this->pendingItem === null) {
            return;
        }

        Gate::authorize('delete', $this->pendingItem);

        $imagePath = $this->pendingItem->image_path;

        $this->pendingItem->delete();

        Storage::disk('public')->delete($imagePath);

        Flux::modal('delete-collection-item-from-card')->close();

        $this->pendingItem = null;

        unset($this->items);

        $this->dispatch('collection-item-deleted');

        Flux::toast(variant: 'success', text: 'Item deleted.');
    }

    private function findItem(int $itemId): CollectionItem
    {
        return CollectionItem::query()
            ->whereBelongsTo($this->collection)
            ->findOrFail($itemId);
    }

    public function render(): View
    {
        return view('livewire.collection-items.index');
    }
}
