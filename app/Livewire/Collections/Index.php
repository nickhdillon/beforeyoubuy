<?php

declare(strict_types=1);

namespace App\Livewire\Collections;

use App\Models\Collection as CollectionModel;
use App\Queries\FilterCollectionQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Title('Collections')]
class Index extends Component
{
    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $visibility = '';

    #[Url(except: '')]
    public string $tagId = '';

    #[Url(except: '')]
    public string $minimumRating = '';

    #[Url(except: '')]
    public string $quantity = '';

    #[Url(except: '')]
    public string $contents = '';

    #[Url(except: 'newest')]
    public string $sort = 'newest';

    /** @var array{visibility: string, tagId: string, minimumRating: string, quantity: string, contents: string, sort: string} */
    public array $filterDraft = [
        'visibility' => '',
        'tagId' => '',
        'minimumRating' => '',
        'quantity' => '',
        'contents' => '',
        'sort' => 'newest',
    ];

    #[Computed]
    #[On('collection-created')]
    public function collections(): Collection
    {
        $query = CollectionModel::query()
            ->whereBelongsTo(auth()->user())
            ->withCount('items')
            ->when(filled($this->search), fn ($query) => $query->search($this->search));

        foreach ($this->activeFilters() as $type => $value) {
            $query = FilterCollectionQuery::apply($query, $type, $value);
        }

        return FilterCollectionQuery::apply($query, 'sort_'.$this->sort, $this->sort)->get();
    }

    #[Computed]
    public function tags(): Collection
    {
        return auth()->user()->tags()->get();
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'visibility', 'tagId', 'minimumRating', 'quantity', 'contents');
        $this->sort = 'newest';
        $this->prepareFilters();
    }

    public function prepareFilters(): void
    {
        $this->filterDraft = [
            'visibility' => $this->visibility,
            'tagId' => $this->tagId,
            'minimumRating' => $this->minimumRating,
            'quantity' => $this->quantity,
            'contents' => $this->contents,
            'sort' => $this->sort,
        ];
    }

    public function clearFilterDraft(): void
    {
        $this->filterDraft = [
            'visibility' => '',
            'tagId' => '',
            'minimumRating' => '',
            'quantity' => '',
            'contents' => '',
            'sort' => 'newest',
        ];
    }

    public function applyFilters(): void
    {
        $this->visibility = $this->filterDraft['visibility'];
        $this->tagId = $this->filterDraft['tagId'];
        $this->minimumRating = $this->filterDraft['minimumRating'];
        $this->quantity = $this->filterDraft['quantity'];
        $this->contents = $this->filterDraft['contents'];
        $this->sort = $this->filterDraft['sort'];
    }

    public function hasActiveFilters(): bool
    {
        return filled($this->search) || filled($this->visibility) || filled($this->tagId)
            || filled($this->minimumRating) || filled($this->quantity) || filled($this->contents)
            || $this->sort !== 'newest';
    }

    /** @return array<string, int|float|string> */
    private function activeFilters(): array
    {
        return array_filter([
            'visibility' => $this->visibility,
            'tag' => filled($this->tagId) ? (int) $this->tagId : null,
            'minimum_rating' => filled($this->minimumRating) ? (float) $this->minimumRating : null,
            'quantity' => $this->quantity,
            'contents' => $this->contents,
        ], fn (mixed $value): bool => filled($value));
    }

    public function render(): View
    {
        return view('livewire.collections.index');
    }
}
