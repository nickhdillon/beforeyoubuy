<?php

declare(strict_types=1);

namespace App\Livewire\WishlistItems;

use App\Actions\MoveCollectionItem;
use App\Models\Collection;
use App\Models\Wishlist;
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

    public ?WishlistItem $pendingItem = null;

    public function mount(): void
    {
        Gate::authorize('update', $this->collection);
    }

    #[Computed]
    public function wishlist(): Wishlist
    {
        return $this->collection->wishlist()->sole();
    }

    #[Computed]
    #[On('wishlist-item-created')]
    #[On('wishlist-item-updated')]
    #[On('wishlist-item-deleted')]
    public function items(): EloquentCollection
    {
        return $this->wishlist->items()->get();
    }

    public function confirmMoveToCollection(int $itemId): void
    {
        $item = $this->findItem($itemId);

        Gate::authorize('update', $item);

        $this->pendingItem = $item;

        Flux::modal('move-wishlist-item-to-collection')->show();
    }

    public function moveToCollection(MoveCollectionItem $moveItem): void
    {
        if ($this->pendingItem === null) {
            return;
        }

        Gate::authorize('update', $this->pendingItem);
        Gate::authorize('update', $this->collection);

        $moveItem->toCollection($this->pendingItem, $this->collection);

        Flux::modal('move-wishlist-item-to-collection')->close();

        $this->pendingItem = null;

        unset($this->items);

        $this->dispatch('wishlist-item-deleted');
        $this->dispatch('collection-item-created');

        Flux::toast(variant: 'success', text: 'Item moved to collection.');
    }

    public function confirmDelete(int $itemId): void
    {
        $item = $this->findItem($itemId);

        Gate::authorize('delete', $item);

        $this->pendingItem = $item;

        Flux::modal('delete-wishlist-item-from-card')->show();
    }

    public function delete(): void
    {
        if ($this->pendingItem === null) {
            return;
        }

        Gate::authorize('delete', $this->pendingItem);

        $imagePath = $this->pendingItem->image_path;

        $this->pendingItem->delete();

        if ($imagePath !== null) {
            Storage::disk('public')->delete($imagePath);
        }

        Flux::modal('delete-wishlist-item-from-card')->close();

        $this->pendingItem = null;

        unset($this->items);

        $this->dispatch('wishlist-item-deleted');

        Flux::toast(variant: 'success', text: 'Wishlist item deleted.');
    }

    private function findItem(int $itemId): WishlistItem
    {
        return WishlistItem::query()
            ->whereBelongsTo($this->wishlist)
            ->findOrFail($itemId);
    }

    public function render(): View
    {
        return view('livewire.wishlist-items.index');
    }
}
