<div class="w-full">
    <header class="flex flex-col gap-4 border-b-2 border-dashed border-emerald-200 pb-8">
        <span class="hard-shadow inline-flex border-2 border-zinc-950 bg-orange-600 px-3 py-2 text-[11px] font-black tracking-[0.12em] text-white uppercase w-fit">
            Your stuff, nicely sorted
        </span>

        <div class="flex mt-2 flex-col sm:flex-row sm:items-center justify-between gap-4">
            <h1 class="text-4xl leading-none font-black tracking-[-0.05em] sm:text-5xl">Collections</h1>
            
            <flux:modal.trigger name="collection-form">
                <flux:button variant="primary" class="w-full sm:w-auto">New collection</flux:button>
            </flux:modal.trigger>
        </div>

        <p class="max-w-xl text-base leading-relaxed font-medium text-zinc-600 sm:text-lg">
            Simple homes for the things you own. Add the details to individual items when they’re useful.
        </p>
    </header>

    <section class="mt-8" aria-labelledby="collection-list-heading">
        <div class="flex items-end justify-between gap-4">
            <h2 id="collection-list-heading" class="text-2xl font-black tracking-tight">Your collections</h2>

            <span class="hard-shadow border-2 border-zinc-950 bg-white px-3 py-1 text-xs font-black">{{ $this->collections->count() }} {{ str('collection')->plural($this->collections->count()) }}</span>
        </div>

        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-end">
            <flux:input
                class="w-full sm:max-w-xl"
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                clearable
                label="Search collections"
                placeholder="Search names, descriptions, items, or tags…"
            />

            <flux:modal.trigger name="collection-filters">
                <flux:button icon="funnel" variant="secondary" class="w-full sm:w-auto" wire:click="prepareFilters">
                    Filters
                    @if ($this->hasActiveFilters())
                        <span class="size-2 bg-orange-600" aria-label="Filters active"></span>
                    @endif
                </flux:button>
            </flux:modal.trigger>
        </div>

        <flux:modal name="collection-filters" flyout variant="floating" class="md:w-96">
            <div class="grid gap-6">
                <div>
                    <flux:heading size="lg" class="font-black!">Filter collections</flux:heading>
                    <flux:text class="mt-2! font-medium! text-zinc-600!">Narrow collections by visibility and the items inside them.</flux:text>
                </div>

                <flux:select variant="listbox" wire:model="filterDraft.visibility" label="Visibility" placeholder="Public or private" clearable>
                    <flux:select.option value="public">Public</flux:select.option>
                    <flux:select.option value="private">Private</flux:select.option>
                </flux:select>

                <flux:select variant="listbox" wire:model="filterDraft.tagId" label="Item tag" placeholder="All tags" clearable>
                    @foreach ($this->tags as $tag)
                        <flux:select.option wire:key="collection-list-filter-tag-{{ $tag->id }}" value="{{ $tag->id }}">{{ $tag->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select variant="listbox" wire:model="filterDraft.minimumRating" label="Item rating" placeholder="Any rating" clearable>
                    <flux:select.option value="4">4+ stars</flux:select.option>
                    <flux:select.option value="3">3+ stars</flux:select.option>
                    <flux:select.option value="2">2+ stars</flux:select.option>
                    <flux:select.option value="1">1+ stars</flux:select.option>
                </flux:select>

                <flux:select variant="listbox" wire:model="filterDraft.quantity" label="Item quantity" placeholder="Any quantity" clearable>
                    <flux:select.option value="single">Has single items</flux:select.option>
                    <flux:select.option value="multiple">Has multiples</flux:select.option>
                </flux:select>

                <flux:select variant="listbox" wire:model="filterDraft.contents" label="Contents" placeholder="Anything" clearable>
                    <flux:select.option value="collection">Has collected items</flux:select.option>
                    <flux:select.option value="wishlist">Has wishlist items</flux:select.option>
                    <flux:select.option value="empty">Empty collections</flux:select.option>
                </flux:select>

                <flux:select variant="listbox" wire:model="filterDraft.sort" label="Sort by">
                    <flux:select.option value="newest">Newest first</flux:select.option>
                    <flux:select.option value="oldest">Oldest first</flux:select.option>
                    <flux:select.option value="name">Name</flux:select.option>
                    <flux:select.option value="items">Most items</flux:select.option>
                </flux:select>

                <div class="flex items-center justify-between gap-3 border-t-2 border-dashed border-emerald-200 pt-5">
                    <flux:button variant="ghost" wire:click="clearFilterDraft">Clear all</flux:button>
                    <flux:modal.close>
                        <flux:button variant="primary" wire:click="applyFilters">Show results</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        </flux:modal>

        @if ($this->collections->isEmpty())
            <div class="hard-shadow mt-5 border-2 border-zinc-950 bg-white p-5 sm:p-8">
                @if ($this->hasActiveFilters())
                    <div class="grid gap-5 border-2 border-dashed border-orange-300 bg-orange-50 p-6 sm:grid-cols-[auto_1fr_auto] sm:items-center">
                        <div class="hard-shadow grid size-16 place-items-center border-2 border-zinc-950 bg-orange-600 text-3xl" aria-hidden="true">⌕</div>
                        <div>
                            <h3 class="text-xl font-black tracking-tight">No matching collections</h3>
                            <p class="mt-2 text-sm leading-relaxed font-medium text-zinc-600">Try changing or clearing one of your filters.</p>
                        </div>
                        <flux:button variant="secondary" class="w-full sm:w-auto" wire:click="clearFilters">Clear search &amp; filters</flux:button>
                    </div>
                @else
                    <div class="grid gap-5 border-2 border-dashed border-emerald-300 bg-emerald-50 p-6 sm:grid-cols-[auto_1fr_auto] sm:items-center">
                        <div class="hard-shadow grid size-16 place-items-center border-2 border-zinc-950 bg-emerald-600 text-3xl" aria-hidden="true">☕</div>
                        <div>
                            <h3 class="text-xl font-black tracking-tight">Start with one simple collection</h3>
                            <p class="mt-2 text-sm leading-relaxed font-medium text-zinc-600">Give it a name now, then snap photos of your items as you go.</p>
                        </div>
                        <flux:modal.trigger name="collection-form">
                            <flux:button variant="secondary" class="w-full sm:w-auto">Create collection</flux:button>
                        </flux:modal.trigger>
                    </div>
                @endif
            </div>
        @else
            <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($this->collections as $collection)
                    <a
                        href="{{ route('collections.show', $collection) }}"
                        wire:key="collection-{{ $collection->id }}"
                        wire:navigate
                        class="hard-shadow hard-shadow-hover group flex min-h-20 flex-col border-2 border-zinc-950 bg-white p-5 transition hover:-translate-y-0.5 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-700"
                    >
                        <div class="pb-2">
                            <div class="flex items-center gap-2 justify-between">
                                <h3 class="text-xl font-black tracking-tight">{{ $collection->name }}</h3>

                                <div class="flex items-start justify-between gap-3">
                                    <span @class(['px-2.5 py-1 text-[11px] font-black uppercase tracking-wide', 'bg-emerald-600 text-white' => $collection->is_public, 'bg-zinc-200 text-zinc-700' => ! $collection->is_public])>{{ $collection->is_public ? 'Public' : 'Private' }}</span>
                                </div>
                            </div>

                            @if ($collection->description)
                                <p class="mt-2 line-clamp-3 text-sm leading-relaxed font-medium text-zinc-600">
                                    {{ $collection->description }}
                                </p>
                            @endif
                        </div>

                        <div class="mt-auto flex items-end justify-between gap-3 border-t-2 border-dashed border-emerald-200">
                            <div class="flex items-center gap-2 text-zinc-500 text-sm font-medium pt-2">
                                <p>{{ $collection->items_count }}</p>
                                <p>{{ str('item')->plural($collection->items_count) }}</p>
                            </div>
                            <span class="text-sm font-black text-emerald-700 group-hover:text-emerald-900">Open →</span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <livewire:collections.form />
</div>
