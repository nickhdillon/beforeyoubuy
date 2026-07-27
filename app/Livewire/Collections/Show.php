<?php

namespace App\Livewire\Collections;

use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\WishlistItem;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.public')]
#[Title('Collection')]
class Show extends Component
{
    public Collection $collection;

    public ?int $wishlistSourceItemId = null;

    public string $wishlistSourceItemName = '';

    public ?int $collectionSourceWishlistItemId = null;

    public string $collectionSourceWishlistItemName = '';

    public ?int $collectionItemPendingDeletionId = null;

    public string $collectionItemPendingDeletionName = '';

    public ?int $wishlistItemPendingDeletionId = null;

    public string $wishlistItemPendingDeletionName = '';

    public function mount(Collection $collection): void
    {
        Gate::authorize('view', $collection);

        $this->collection = $collection->load(['user', 'items']);

        if (Gate::allows('update', $collection)) {
            $this->collection->load('wishlist.items');
        }
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

    #[On('collection-item-deleted')]
    public function refreshDeletedItems(): void
    {
        $this->refreshItems();
    }

    #[On('collection-updated')]
    public function refreshCollection(): void
    {
        $this->collection->refresh()->load(['user', 'items']);

        if (Gate::allows('update', $this->collection)) {
            $this->collection->load('wishlist.items');
        }
    }

    #[On('wishlist-item-created')]
    #[On('wishlist-item-updated')]
    #[On('wishlist-item-deleted')]
    public function refreshWishlist(): void
    {
        Gate::authorize('update', $this->collection);

        $this->collection->load('wishlist.items');
    }

    public function confirmMoveToWishlist(int $itemId): void
    {
        $item = CollectionItem::query()
            ->whereBelongsTo($this->collection)
            ->findOrFail($itemId);

        Gate::authorize('update', $item);

        $this->wishlistSourceItemId = $item->id;
        $this->wishlistSourceItemName = $item->name ?: 'this item';

        Flux::modal('move-item-to-wishlist')->show();
    }

    public function moveToWishlist(): void
    {
        $itemId = $this->wishlistSourceItemId;
        assert(is_int($itemId));

        $item = CollectionItem::query()
            ->whereBelongsTo($this->collection)
            ->findOrFail($itemId);

        Gate::authorize('update', $item);

        $wishlist = $this->collection->wishlist()->sole();
        Gate::authorize('create', [WishlistItem::class, $wishlist]);

        $wishlistImagePath = $this->moveImage($item->image_path, 'wishlist-items');
        $disk = Storage::disk('public');

        try {
            DB::transaction(function () use ($wishlist, $wishlistImagePath, $item): void {
                $wishlist->items()->create([
                    'image_path' => $wishlistImagePath,
                    'name' => $item->name,
                    'url' => $item->url,
                    'quantity' => $item->quantity,
                    'rating' => $item->rating,
                    'notes' => $item->notes,
                ]);

                $item->delete();
            });
        } catch (\Throwable $exception) {
            $disk->move($wishlistImagePath, $item->image_path);

            throw $exception;
        }

        Flux::modal('move-item-to-wishlist')->close();

        $this->reset(['wishlistSourceItemId', 'wishlistSourceItemName']);
        $this->collection->load(['items', 'wishlist.items']);

        Flux::toast(variant: 'success', text: 'Item moved to wishlist.');
    }

    public function confirmMoveToCollection(int $wishlistItemId): void
    {
        $wishlist = $this->collection->wishlist()->sole();
        $wishlistItem = WishlistItem::query()
            ->whereBelongsTo($wishlist)
            ->findOrFail($wishlistItemId);

        Gate::authorize('update', $wishlistItem);

        $this->collectionSourceWishlistItemId = $wishlistItem->id;
        $this->collectionSourceWishlistItemName = $wishlistItem->name ?: 'this item';

        Flux::modal('move-wishlist-item-to-collection')->show();
    }

    public function moveToCollection(): void
    {
        $wishlistItemId = $this->collectionSourceWishlistItemId;
        assert(is_int($wishlistItemId));

        $wishlist = $this->collection->wishlist()->sole();
        $wishlistItem = WishlistItem::query()
            ->whereBelongsTo($wishlist)
            ->findOrFail($wishlistItemId);

        Gate::authorize('update', $wishlistItem);
        Gate::authorize('update', $this->collection);

        $sourceImagePath = $wishlistItem->image_path;
        throw_unless(is_string($sourceImagePath), \RuntimeException::class, 'The wishlist item needs a photo before it can be added to the collection.');

        $collectionImagePath = $this->moveImage($sourceImagePath, 'collection-items');
        $disk = Storage::disk('public');

        try {
            DB::transaction(function () use ($collectionImagePath, $wishlistItem): void {
                $this->collection->items()->create([
                    'image_path' => $collectionImagePath,
                    'name' => $wishlistItem->name,
                    'url' => $wishlistItem->url,
                    'quantity' => $wishlistItem->quantity,
                    'rating' => $wishlistItem->rating,
                    'notes' => $wishlistItem->notes,
                ]);

                $wishlistItem->delete();
            });
        } catch (\Throwable $exception) {
            $disk->move($collectionImagePath, $sourceImagePath);

            throw $exception;
        }

        Flux::modal('move-wishlist-item-to-collection')->close();

        $this->reset(['collectionSourceWishlistItemId', 'collectionSourceWishlistItemName']);
        $this->collection->load(['items', 'wishlist.items']);

        Flux::toast(variant: 'success', text: 'Item moved to collection.');
    }

    public function confirmDeleteCollectionItem(int $itemId): void
    {
        $item = CollectionItem::query()
            ->whereBelongsTo($this->collection)
            ->findOrFail($itemId);

        Gate::authorize('delete', $item);

        $this->collectionItemPendingDeletionId = $item->id;
        $this->collectionItemPendingDeletionName = $item->name ?: 'this item';

        Flux::modal('delete-collection-item-from-card')->show();
    }

    public function deleteCollectionItem(): void
    {
        $itemId = $this->collectionItemPendingDeletionId;
        throw_unless(is_int($itemId), \LogicException::class);

        $item = CollectionItem::query()
            ->whereBelongsTo($this->collection)
            ->findOrFail($itemId);

        Gate::authorize('delete', $item);

        $imagePath = $item->image_path;

        $item->delete();
        Storage::disk('public')->delete($imagePath);

        Flux::modal('delete-collection-item-from-card')->close();

        $this->reset(['collectionItemPendingDeletionId', 'collectionItemPendingDeletionName']);
        $this->collection->load('items');

        Flux::toast(variant: 'success', text: 'Item deleted.');
    }

    public function confirmDeleteWishlistItem(int $wishlistItemId): void
    {
        $wishlist = $this->collection->wishlist()->sole();
        $wishlistItem = WishlistItem::query()
            ->whereBelongsTo($wishlist)
            ->findOrFail($wishlistItemId);

        Gate::authorize('delete', $wishlistItem);

        $this->wishlistItemPendingDeletionId = $wishlistItem->id;
        $this->wishlistItemPendingDeletionName = $wishlistItem->name ?: 'this item';

        Flux::modal('delete-wishlist-item-from-card')->show();
    }

    public function deleteWishlistItem(): void
    {
        $wishlistItemId = $this->wishlistItemPendingDeletionId;
        throw_unless(is_int($wishlistItemId), \LogicException::class);

        $wishlist = $this->collection->wishlist()->sole();
        $wishlistItem = WishlistItem::query()
            ->whereBelongsTo($wishlist)
            ->findOrFail($wishlistItemId);

        Gate::authorize('delete', $wishlistItem);

        $imagePath = $wishlistItem->image_path;

        $wishlistItem->delete();

        if ($imagePath !== null) {
            Storage::disk('public')->delete($imagePath);
        }

        Flux::modal('delete-wishlist-item-from-card')->close();

        $this->reset(['wishlistItemPendingDeletionId', 'wishlistItemPendingDeletionName']);
        $this->collection->load('wishlist.items');

        Flux::toast(variant: 'success', text: 'Wishlist item deleted.');
    }

    public function render(): View
    {
        return view('livewire.collections.show');
    }

    private function moveImage(string $sourcePath, string $directory): string
    {
        $extension = pathinfo($sourcePath, PATHINFO_EXTENSION);
        $destinationPath = $directory.'/'.Str::uuid().($extension !== '' ? ".{$extension}" : '');

        throw_unless(
            Storage::disk('public')->move($sourcePath, $destinationPath),
            \RuntimeException::class,
            'The item photo could not be moved.',
        );

        return $destinationPath;
    }
}
