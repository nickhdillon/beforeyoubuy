<?php

declare(strict_types=1);

namespace App\Livewire\CollectionItems;

use App\Actions\MoveCollectionItem;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\WishlistItem;
use App\Queries\FilterItemQuery;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    public Collection $collection;

    #[Locked]
    public bool $publicView = false;

    #[Url(as: 'items', except: '')]
    public string $search = '';

    #[Url(as: 'item-tag', except: '')]
    public string $tagId = '';

    #[Url(as: 'item-rating', except: '')]
    public string $minimumRating = '';

    #[Url(as: 'item-quantity', except: '')]
    public string $quantity = '';

    #[Url(as: 'item-link', except: '')]
    public string $link = '';

    #[Url(as: 'item-sort', except: 'newest')]
    public string $sort = 'newest';

    /** @var array{tagId: string, minimumRating: string, quantity: string, link: string, sort: string} */
    public array $filterDraft = [
        'tagId' => '',
        'minimumRating' => '',
        'quantity' => '',
        'link' => '',
        'sort' => 'newest',
    ];

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
        $query = CollectionItem::query()
            ->whereBelongsTo($this->collection)
            ->with('tags')
            ->when(filled($this->search), fn ($query) => $query->search($this->search));

        foreach ($this->activeFilters() as $type => $value) {
            $query = FilterItemQuery::apply($query, $type, $value);
        }

        $query = FilterItemQuery::apply($query, 'sort_'.$this->sort, $this->sort);

        return $query
            ->get()
            ->each(fn (CollectionItem $item): CollectionItem => $item->setRelation('collection', $this->collection));
    }

    #[Computed]
    public function tags(): EloquentCollection
    {
        return $this->collection->user->tags;
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'tagId', 'minimumRating', 'quantity', 'link');
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
            'sort' => 'newest',
        ];
    }

    public function applyFilters(): void
    {
        $this->tagId = $this->filterDraft['tagId'];
        $this->minimumRating = $this->filterDraft['minimumRating'];
        $this->quantity = $this->filterDraft['quantity'];
        $this->link = $this->filterDraft['link'];
        $this->sort = $this->filterDraft['sort'];
    }

    public function hasActiveFilters(): bool
    {
        return filled($this->search) || filled($this->tagId) || filled($this->minimumRating)
            || filled($this->quantity) || filled($this->link) || $this->sort !== 'newest';
    }

    /** @return array<string, int|float|string> */
    private function activeFilters(): array
    {
        return array_filter([
            'tag' => filled($this->tagId) ? (int) $this->tagId : null,
            'minimum_rating' => filled($this->minimumRating) ? (float) $this->minimumRating : null,
            'quantity' => $this->quantity,
            'link' => $this->link,
        ], fn (mixed $value): bool => filled($value));
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
