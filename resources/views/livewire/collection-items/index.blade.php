<div>
    <section class="mt-8" aria-labelledby="items-heading">
        <div class="flex items-end justify-between gap-4">
            <h2 id="items-heading" class="text-2xl font-black tracking-tight">Items</h2>

            <span class="hard-shadow border-2 border-zinc-950 bg-white px-3 py-1 text-xs font-black">
                {{ $this->items->count() }} {{ str('item')->plural($this->items->count()) }}
            </span>
        </div>

        <div class="mt-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <flux:input
                class="w-full sm:w-[375px]"
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                clearable
                placeholder="Search names, notes, links, or tags…"
            />

            <flux:modal.trigger name="collection-item-filters">
                <flux:button icon="funnel" variant="secondary" class="w-full sm:w-auto" wire:click="prepareFilters">
                    Filters
                    @if ($this->hasActiveFilters())
                        <span class="size-2 bg-orange-600" aria-label="Filters active"></span>
                    @endif
                </flux:button>
            </flux:modal.trigger>
        </div>

        <flux:modal name="collection-item-filters" flyout variant="floating" class="md:w-96">
            <div class="grid gap-6">
                <div>
                    <flux:heading size="lg" class="font-black!">Filter collection items</flux:heading>
                    <flux:text class="mt-2! font-medium! text-zinc-600!">Narrow this collection by its item details.</flux:text>
                </div>

                <flux:select variant="listbox" wire:model="filterDraft.tagId" label="Tag" placeholder="All tags" clearable>
                    @foreach ($this->tags as $tag)
                        <flux:select.option wire:key="collection-filter-tag-{{ $tag->id }}" value="{{ $tag->id }}">{{ $tag->name }}</flux:select.option>
                    @endforeach
                </flux:select>

                <flux:select variant="listbox" wire:model="filterDraft.minimumRating" label="Rating" placeholder="Any rating" clearable>
                    <flux:select.option value="4">4+ stars</flux:select.option>
                    <flux:select.option value="3">3+ stars</flux:select.option>
                    <flux:select.option value="2">2+ stars</flux:select.option>
                    <flux:select.option value="1">1+ stars</flux:select.option>
                </flux:select>

                <flux:select variant="listbox" wire:model="filterDraft.quantity" label="Quantity" placeholder="Any quantity" clearable>
                    <flux:select.option value="single">Single items</flux:select.option>
                    <flux:select.option value="multiple">Multiple items</flux:select.option>
                </flux:select>

                <flux:select variant="listbox" wire:model="filterDraft.link" label="Link" placeholder="With or without" clearable>
                    <flux:select.option value="with">Has a link</flux:select.option>
                    <flux:select.option value="without">No link</flux:select.option>
                </flux:select>

                <flux:select variant="listbox" wire:model="filterDraft.sort" label="Sort by">
                    <flux:select.option value="newest">Newest first</flux:select.option>
                    <flux:select.option value="oldest">Oldest first</flux:select.option>
                    <flux:select.option value="name">Name</flux:select.option>
                    <flux:select.option value="rating">Highest rated</flux:select.option>
                    <flux:select.option value="quantity">Highest quantity</flux:select.option>
                </flux:select>

                <div class="flex items-center justify-between gap-3 border-t-2 border-dashed border-emerald-200 pt-5">
                    <flux:button variant="ghost" wire:click="clearFilterDraft">Clear all</flux:button>
                    <flux:modal.close>
                        <flux:button variant="primary" wire:click="applyFilters">Show results</flux:button>
                    </flux:modal.close>
                </div>
            </div>
        </flux:modal>

        @if ($this->items->isEmpty())
            <div class="hard-shadow mt-5 border-2 border-zinc-950 bg-white p-5 sm:p-8">
                <div class="grid gap-5 border-2 border-dashed border-emerald-300 bg-emerald-50 p-6 sm:grid-cols-[auto_1fr_auto] sm:items-center">
                    <div class="hard-shadow grid size-16 place-items-center border-2 border-zinc-950 bg-orange-600 text-3xl" aria-hidden="true">📷</div>

                    <div>
                        <h3 class="text-xl font-black tracking-tight">
                            {{ $this->hasActiveFilters() ? 'No matching items' : 'Nothing collected yet' }}
                        </h3>

                        <p class="mt-2 text-sm leading-relaxed font-medium text-zinc-600">
                            {{ $this->hasActiveFilters() ? 'Try changing or clearing one of your filters.' : 'A photo is all it takes. Names, ratings, and other details can wait.' }}
                        </p>
                    </div>

                    @if ($this->hasActiveFilters())
                        <flux:button variant="secondary" class="w-full sm:w-auto" wire:click="clearFilters">Clear search &amp; filters</flux:button>
                    @else
                        @can('update', $collection)
                        <flux:modal.trigger name="collection-item-form">
                            <flux:button variant="secondary" class="w-full sm:w-auto">Take a photo</flux:button>
                        </flux:modal.trigger>
                        @endcan
                    @endif
                </div>
            </div>
        @else
            <div class="mt-5 grid grid-cols-2 gap-5 lg:grid-cols-3">
                @foreach ($this->items as $item)
                    <article wire:key="collection-item-{{ $item->id }}" class="hard-shadow hard-shadow-hover group relative flex min-w-0 flex-col border-2 border-zinc-950 bg-white transition hover:-translate-y-0.5">
                        @can('update', $item)
                            <button
                                type="button"
                                class="absolute inset-0 z-10 cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-700"
                                x-on:click="$dispatch('edit-collection-item', { itemId: {{ $item->id }} })"
                                aria-label="Edit {{ $item->name ?: 'item' }}"
                            ></button>

                            <div class="absolute top-3 end-3 z-20">
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button
                                        square
                                        size="sm"
                                        variant="secondary"
                                        icon="ellipsis-horizontal"
                                        aria-label="Actions for {{ $item->name ?: 'item' }}"
                                        class="hard-shadow px-4!"
                                    />

                                    <flux:menu class="hard-shadow min-w-48 rounded-none! border-2! border-zinc-950! bg-white! p-1!">
                                        <flux:menu.item
                                            class="rounded-none! px-2.5! py-2! font-black! text-zinc-950! data-active:bg-orange-100/65!"
                                            x-on:click="$dispatch('edit-collection-item', { itemId: {{ $item->id }} })"
                                        >
                                            Edit item
                                        </flux:menu.item>

                                        <flux:menu.item
                                            class="rounded-none! px-2.5! py-2! font-black! text-zinc-950! data-active:bg-orange-100/65!"
                                            wire:click="confirmMoveToWishlist({{ $item->id }})"
                                        >
                                            Move to wishlist
                                        </flux:menu.item>

                                        <flux:menu.item
                                            variant="danger"
                                            class="rounded-none! px-2.5! py-2! font-black! text-red-700! data-active:bg-orange-100/65!"
                                            wire:click="confirmDelete({{ $item->id }})"
                                        >
                                            Delete item
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </div>
                        @endcan

                        @if ($item->quantity > 1)
                            <span
                                class="hard-shadow absolute top-3 start-3 z-20 border-2 border-zinc-950 bg-white px-2.5 py-1 text-xs font-black"
                                aria-label="Quantity {{ $item->quantity }}"
                            >
                                ×{{ Number::format($item->quantity) }}
                            </span>
                        @endif

                        <div class="hard-shadow m-3 mb-0 overflow-hidden border-2 border-zinc-950 bg-emerald-50">
                            <img
                                src="{{ Storage::disk('public')->url($item->image_path) }}"
                                alt="{{ $item->name ?: 'Collection item' }}"
                                class="aspect-square w-full object-cover transition duration-300 group-hover:saturate-110"
                            />
                        </div>

                        <div class="flex min-w-0 flex-1 flex-col p-4">
                            @if ($item->name || $item->rating)
                                <div class="flex min-w-0 items-center justify-between gap-3">
                                    @if ($item->name)
                                        <h3 class="min-w-0 truncate text-lg leading-tight font-black tracking-tight">
                                            {{ $item->name }}
                                        </h3>
                                    @endif

                                    @if ($item->rating)
                                        <div
                                            class="flex shrink-0 items-center gap-1.5 text-xs font-black text-zinc-700"
                                            aria-label="Rated {{ $item->rating }} out of 5"
                                        >
                                            <span class="text-orange-600" aria-hidden="true">★</span>
                                            <span>
                                                {{ (float) $item->rating === (float) (int) $item->rating ? Number::format($item->rating) : Number::format($item->rating, 1) }}
                                            </span>
                                            <span class="font-bold text-zinc-400">/ 5</span>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if ($item->tags->isNotEmpty())
                                <div class="mt-3 flex flex-wrap gap-1.5">
                                    @foreach ($item->tags as $tag)
                                        <span wire:key="collection-item-{{ $item->id }}-tag-{{ $tag->id }}" class="border border-emerald-700 bg-emerald-50 px-2 py-0.5 text-[11px] font-black text-emerald-800">{{ $tag->name }}</span>
                                    @endforeach
                                </div>
                            @endif

                            @if ($item->notes)
                                <p class="mt-2 line-clamp-2 text-sm leading-relaxed font-medium text-zinc-600">
                                    {{ $item->notes }}
                                </p>
                            @endif
                        </div>

                        @if ($item->url)
                            <a
                                href="{{ $item->url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="relative z-20 border-t-2 border-zinc-950 bg-emerald-50 px-4 py-3 text-sm font-black text-emerald-700 hover:text-emerald-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                            >
                                Open link ↗
                            </a>
                        @endif
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    @can('update', $collection)
        <flux:modal name="move-item-to-wishlist" class="max-w-md" wire:close="$set('pendingItem', null)">
            <div class="grid gap-6">
                <div>
                    <flux:heading size="lg" class="font-black!">
                        Move to wishlist?
                    </flux:heading>

                    <flux:text class="mt-2! font-medium! text-zinc-600!">
                        Move {{ $pendingItem?->name ?: 'this item' }} to this collection’s private wishlist? It will no longer appear in the collection.
                    </flux:text>
                </div>

                <div class="flex justify-end gap-3">
                    <flux:modal.close>
                        <flux:button type="button" variant="ghost" class="w-full sm:w-auto">Cancel</flux:button>
                    </flux:modal.close>

                    <flux:button
                        type="button"
                        variant="primary"
                        wire:click="moveToWishlist"
                        wire:loading.attr="disabled"
                        wire:target="moveToWishlist"
                    >
                        <span wire:loading.remove wire:target="moveToWishlist">Move</span>
                        <span wire:loading wire:target="moveToWishlist">Moving…</span>
                    </flux:button>
                </div>
            </div>
        </flux:modal>

        <flux:modal name="delete-collection-item-from-card" class="max-w-md" wire:close="$set('pendingItem', null)">
            <div class="grid gap-6">
                <div>
                    <flux:heading size="lg" class="font-black!">
                        Delete item?
                    </flux:heading>

                    <flux:text class="mt-2! font-medium! text-zinc-600!">
                        Permanently delete {{ $pendingItem?->name ?: 'this item' }} and its photo? This cannot be undone.
                    </flux:text>
                </div>

                <div class="flex justify-end gap-3">
                    <flux:modal.close>
                        <flux:button type="button" variant="ghost">Cancel</flux:button>
                    </flux:modal.close>

                    <flux:button
                        type="button"
                        variant="danger"
                        wire:click="delete"
                        wire:loading.attr="disabled"
                        wire:target="delete"
                    >
                        <span wire:loading.remove wire:target="delete">Delete</span>
                        <span wire:loading wire:target="delete">Deleting…</span>
                    </flux:button>
                </div>
            </div>
        </flux:modal>

        <livewire:collection-items.form :$collection />
    @endcan
</div>
