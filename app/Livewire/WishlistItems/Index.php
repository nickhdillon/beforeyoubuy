<?php

declare(strict_types=1);

namespace App\Livewire\WishlistItems;

use App\Actions\MoveCollectionItem;
use App\Models\Collection;
use App\Models\Wishlist;
use App\Models\WishlistItem;
use App\Queries\FilterItemQuery;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    public Collection $collection;

    public Wishlist $wishlist;

    #[Url(as: 'wishlist', except: '')]
    public string $search = '';

    #[Url(as: 'wishlist-tag', except: '')]
    public string $tagId = '';

    #[Url(as: 'wishlist-rating', except: '')]
    public string $minimumRating = '';

    #[Url(as: 'wishlist-quantity', except: '')]
    public string $quantity = '';

    #[Url(as: 'wishlist-link', except: '')]
    public string $link = '';

    #[Url(as: 'wishlist-photo', except: '')]
    public string $photo = '';

    #[Url(as: 'wishlist-sort', except: 'newest')]
    public string $sort = 'newest';

    /** @var array{tagId: string, minimumRating: string, quantity: string, link: string, photo: string, sort: string} */
    public array $filterDraft = [
        'tagId' => '',
        'minimumRating' => '',
        'quantity' => '',
        'link' => '',
        'photo' => '',
        'sort' => 'newest',
    ];

    public ?WishlistItem $pendingItem = null;

    public function mount(?Wishlist $wishlist = null): void
    {
        Gate::authorize('update', $this->collection);
        $this->wishlist = $wishlist ?? $this->collection->wishlist()->sole();
    }

    #[Computed]
    #[On('wishlist-item-created')]
    #[On('wishlist-item-updated')]
    #[On('wishlist-item-deleted')]
    public function items(): EloquentCollection
    {
        $query = WishlistItem::query()
            ->whereBelongsTo($this->wishlist)
            ->with('tags')
            ->when(filled($this->search), fn ($query) => $query->search($this->search));

        foreach ($this->activeFilters() as $type => $value) {
            $query = FilterItemQuery::apply($query, $type, $value);
        }

        return FilterItemQuery::apply($query, 'sort_'.$this->sort, $this->sort)->get();
    }

    #[Computed]
    public function tags(): EloquentCollection
    {
        return $this->collection->user->tags;
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'tagId', 'minimumRating', 'quantity', 'link', 'photo');
        $this->sort = 'newest';
        $this->prepareFilters();
    }

    public function prepareFilters(): void
    {
        $this->filterDraft = [
            'tagId' => $this->tagId,
            'minimumRating' => $this->minimumRating,
            'quantity' => $this->quantity,
            'link' => $this->link,
            'photo' => $this->photo,
            'sort' => $this->sort,
        ];
    }

    public function clearFilterDraft(): void
    {
        $this->filterDraft = [
            'tagId' => '',
            'minimumRating' => '',
            'quantity' => '',
            'link' => '',
            'photo' => '',
            'sort' => 'newest',
        ];
    }

    public function applyFilters(): void
    {
        $this->tagId = $this->filterDraft['tagId'];
        $this->minimumRating = $this->filterDraft['minimumRating'];
        $this->quantity = $this->filterDraft['quantity'];
        $this->link = $this->filterDraft['link'];
        $this->photo = $this->filterDraft['photo'];
        $this->sort = $this->filterDraft['sort'];
    }

    public function hasActiveFilters(): bool
    {
        return filled($this->search) || filled($this->tagId) || filled($this->minimumRating)
            || filled($this->quantity) || filled($this->link) || filled($this->photo) || $this->sort !== 'newest';
    }

    /** @return array<string, int|float|string> */
    private function activeFilters(): array
    {
        return array_filter([
            'tag' => filled($this->tagId) ? (int) $this->tagId : null,
            'minimum_rating' => filled($this->minimumRating) ? (float) $this->minimumRating : null,
            'quantity' => $this->quantity,
            'link' => $this->link,
            'photo' => $this->photo,
        ], fn (mixed $value): bool => filled($value));
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
