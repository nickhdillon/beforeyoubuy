<div class="w-full">
    <a
        href="{{ auth()->check() ? route('collections.index') : route('home') }}"
        class="inline-flex items-center gap-2 text-sm font-black text-emerald-700 hover:text-emerald-900"
        wire:navigate
    >
        ← {{ auth()->check() ? 'Back to collections' : 'Before You Buy' }}
    </a>

    <header class="mt-6 flex flex-col gap-4 border-b-2 border-dashed border-emerald-200 pb-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="min-w-0">
                @if (auth()->id() !== $collection->user_id)
                    <p class="mb-3 flex items-center gap-2 text-sm font-bold text-zinc-600">
                        <span class="grid size-7 shrink-0 place-items-center bg-orange-600 text-[10px] font-black text-white" aria-hidden="true">
                            {{ $collection->user->initials() }}
                        </span>

                        <span>
                            Collection by <span class="font-black text-zinc-950">{{ $collection->user->name }}</span>
                        </span>
                    </p>
                @endif

                <div class="flex w-fit max-w-full items-center gap-4">
                    <h1 class="min-w-0 text-4xl leading-none font-black tracking-[-0.05em] break-words sm:text-5xl">
                        {{ $collection->name }}
                    </h1>

                    @can('update', $collection)
                        @if ($collection->is_public)
                            <div
                                class="relative shrink-0"
                                x-data="{ copied: false, resetTimer: null }"
                            >
                                <flux:button
                                    square
                                    variant="secondary"
                                    tooltip="Copy public link"
                                    aria-label="Copy public link"
                                    x-on:click="
                                        navigator.clipboard.writeText(@js(route('collections.show', $collection))).then(() => {
                                            copied = true
                                            clearTimeout(resetTimer)
                                            resetTimer = setTimeout(() => copied = false, 2000)
                                        })
                                    "
                                >
                                    <span class="relative block size-5.5 shrink-0">
                                        <flux:icon.clipboard-document
                                            class="absolute inset-0 size-5.5"
                                            x-show="! copied"
                                            x-transition:enter="transition-opacity duration-200"
                                            x-transition:enter-start="opacity-0"
                                            x-transition:enter-end="opacity-100"
                                            x-transition:leave="transition-opacity duration-200"
                                            x-transition:leave-start="opacity-100"
                                            x-transition:leave-end="opacity-0"
                                        />

                                        <flux:icon.clipboard-document-check
                                            x-cloak
                                            class="absolute inset-0 size-5.5"
                                            x-show="copied"
                                            x-transition:enter="transition-opacity duration-200"
                                            x-transition:enter-start="opacity-0"
                                            x-transition:enter-end="opacity-100"
                                            x-transition:leave="transition-opacity duration-200"
                                            x-transition:leave-start="opacity-100"
                                            x-transition:leave-end="opacity-0"
                                        />
                                    </span>
                                </flux:button>

                                <div
                                    x-cloak
                                    x-show="copied"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="translate-y-1 scale-95 opacity-0"
                                    x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                                    x-transition:leave="transition ease-in duration-150"
                                    x-transition:leave-start="translate-y-0 scale-100 opacity-100"
                                    x-transition:leave-end="translate-y-1 scale-95 opacity-0"
                                    class="hard-shadow absolute top-full end-0 z-20 mt-2 max-w-[calc(100vw-2rem)] whitespace-nowrap border-2 border-zinc-950 bg-zinc-950 px-3 py-2 text-xs font-black text-white"
                                    role="status"
                                    aria-live="polite"
                                >
                                    Copied to clipboard
                                </div>
                            </div>
                        @endif
                    @endcan
                </div>
            </div>

            @can('update', $collection)
                <div class="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                    <flux:modal.trigger name="collection-form">
                        <flux:button variant="secondary" class="w-full sm:w-auto">Edit collection</flux:button>
                    </flux:modal.trigger>

                    <flux:modal.trigger name="collection-item-form">
                        <flux:button variant="primary" class="w-full sm:w-auto">Add item</flux:button>
                    </flux:modal.trigger>
                </div>
            @endcan
        </div>

        @if ($collection->description)
            <p class="max-w-2xl text-base leading-relaxed font-medium text-zinc-600 sm:text-lg">
                {{ $collection->description }}
            </p>
        @endif
    </header>

    <section class="mt-8" aria-labelledby="items-heading">
        <div class="flex items-end justify-between gap-4">
            <h2 id="items-heading" class="text-2xl font-black tracking-tight">Items</h2>

            <span class="hard-shadow border-2 border-zinc-950 bg-white px-3 py-1 text-xs font-black">
                {{ $collection->items->count() }} {{ str('item')->plural($collection->items->count()) }}
            </span>
        </div>

        @if ($collection->items->isEmpty())
            <div class="hard-shadow mt-5 border-2 border-zinc-950 bg-white p-5 sm:p-8">
                <div class="grid gap-5 border-2 border-dashed border-emerald-300 bg-emerald-50 p-6 sm:grid-cols-[auto_1fr_auto] sm:items-center">
                    <div class="hard-shadow grid size-16 place-items-center border-2 border-zinc-950 bg-orange-600 text-3xl" aria-hidden="true">📷</div>

                    <div>
                        <h3 class="text-xl font-black tracking-tight">
                            Nothing collected yet
                        </h3>

                        <p class="mt-2 text-sm leading-relaxed font-medium text-zinc-600">
                            A photo is all it takes. Names, ratings, and other details can wait.
                        </p>
                    </div>

                    @can('update', $collection)
                        <flux:modal.trigger name="collection-item-form">
                            <flux:button variant="secondary" class="w-full sm:w-auto">Take a photo</flux:button>
                        </flux:modal.trigger>
                    @endcan
                </div>
            </div>
        @else
            <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($collection->items as $item)
                    <article wire:key="collection-item-{{ $item->id }}" class="hard-shadow hard-shadow-hover group relative flex min-w-0 flex-col border-2 border-zinc-950 bg-white transition hover:-translate-y-0.5">
                        @can('update', $item)
                            <button
                                type="button"
                                class="absolute inset-0 z-10 cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-700"
                                x-on:click="$dispatch('edit-collection-item', { itemId: {{ $item->id }} })"
                                aria-label="Edit {{ $item->name ?: 'untitled item' }}"
                            ></button>

                            <div class="absolute top-3 end-3 z-20">
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button
                                        square
                                        size="sm"
                                        variant="secondary"
                                        icon="ellipsis-horizontal"
                                        aria-label="Actions for {{ $item->name ?: 'untitled item' }}"
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
                                            wire:click="confirmDeleteCollectionItem({{ $item->id }})"
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

                        <div class="overflow-hidden border-b-2 border-zinc-950 bg-emerald-50">
                            <img
                                src="{{ Storage::disk('public')->url($item->image_path) }}"
                                alt="{{ $item->name ?: 'Collection item' }}"
                                class="aspect-4/3 w-full object-cover transition duration-300 group-hover:scale-[1.02] group-hover:saturate-110"
                            />
                        </div>

                        <div class="flex min-w-0 flex-1 flex-col p-4">
                            <div class="flex min-w-0 items-center justify-between gap-3">
                                <h3 class="min-w-0 truncate text-lg leading-tight font-black tracking-tight">
                                    {{ $item->name ?: 'Untitled item' }}
                                </h3>

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
        <section class="mt-12 border-t-2 border-dashed border-emerald-200 pt-8" aria-labelledby="wishlist-heading">
            <div class="flex flex-col">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <h2 id="wishlist-heading" class="text-2xl font-black tracking-tight">
                            Wishlist
                        </h2>

                        <span class="border-2 border-zinc-950 bg-zinc-950 px-2 py-1 text-[10px] font-black tracking-wide text-white">
                            Private
                        </span>
                    </div>

                    <flux:modal.trigger name="wishlist-item-form">
                        <flux:button variant="secondary" class="w-full sm:w-auto">Add wishlist item</flux:button>
                    </flux:modal.trigger>
                </div>

                <p class="mt-4 sm:mt-2 max-w-xl text-sm leading-relaxed font-medium text-zinc-600">
                    Keep track of what you might buy next. Your wishlist is visible only to you, even when this collection is public.
                </p>
            </div>

            @if ($collection->wishlist->items->isEmpty())
                <div class="hard-shadow mt-5 border-2 border-zinc-950 bg-white p-5 sm:p-8">
                    <div class="grid gap-5 border-2 border-dashed border-orange-300 bg-orange-50 p-6 sm:grid-cols-[auto_1fr_auto] sm:items-center">
                        <div class="hard-shadow grid size-16 place-items-center border-2 border-zinc-950 bg-orange-600 text-3xl" aria-hidden="true">✨</div>

                        <div>
                            <h3 class="text-xl font-black tracking-tight">
                                Nothing on your wishlist
                            </h3>

                            <p class="mt-2 text-sm leading-relaxed font-medium text-zinc-600">
                                Save an idea here before it turns into an impulse purchase.
                            </p>
                        </div>

                        <flux:modal.trigger name="wishlist-item-form">
                            <flux:button variant="secondary" class="w-full sm:w-auto">Add your first item</flux:button>
                        </flux:modal.trigger>
                    </div>
                </div>
            @else
                <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($collection->wishlist->items as $wishlistItem)
                        <article wire:key="wishlist-item-{{ $wishlistItem->id }}" class="hard-shadow hard-shadow-hover group relative flex min-w-0 flex-col border-2 border-zinc-950 bg-white transition hover:-translate-y-0.5">
                            <button
                                type="button"
                                class="absolute inset-0 z-10 cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-orange-600"
                                x-on:click="$dispatch('edit-wishlist-item', { itemId: {{ $wishlistItem->id }} })"
                                aria-label="Edit {{ $wishlistItem->name ?: 'untitled wishlist item' }}"
                            ></button>

                            <div class="absolute top-3 end-3 z-20">
                                <flux:dropdown position="bottom" align="end">
                                    <flux:button
                                        square
                                        size="sm"
                                        variant="secondary"
                                        icon="ellipsis-horizontal"
                                        aria-label="Actions for {{ $wishlistItem->name ?: 'untitled wishlist item' }}"
                                        class="hard-shadow px-4!"
                                    />

                                    <flux:menu class="hard-shadow min-w-48 rounded-none! border-2! border-zinc-950! bg-white! p-1!">
                                        <flux:menu.item
                                            class="rounded-none! px-2.5! py-2! font-black! text-zinc-950! data-active:bg-orange-100/65!"
                                            x-on:click="$dispatch('edit-wishlist-item', { itemId: {{ $wishlistItem->id }} })"
                                        >
                                            Edit item
                                        </flux:menu.item>

                                        <flux:menu.item
                                            class="rounded-none! px-2.5! py-2! font-black! text-zinc-950! data-active:bg-orange-100/65!"
                                            wire:click="confirmMoveToCollection({{ $wishlistItem->id }})"
                                        >
                                            Move to collection
                                        </flux:menu.item>

                                        <flux:menu.item
                                            variant="danger"
                                            class="rounded-none! px-2.5! py-2! font-black! text-red-700! data-active:bg-orange-100/65!"
                                            wire:click="confirmDeleteWishlistItem({{ $wishlistItem->id }})"
                                        >
                                            Delete item
                                        </flux:menu.item>
                                    </flux:menu>
                                </flux:dropdown>
                            </div>

                            @if ($wishlistItem->quantity > 1)
                                <span
                                    class="hard-shadow absolute top-3 start-3 z-20 border-2 border-zinc-950 bg-white px-2.5 py-1 text-xs font-black"
                                    aria-label="Quantity {{ $wishlistItem->quantity }}"
                                >
                                    ×{{ Number::format($wishlistItem->quantity) }}
                                </span>
                            @endif

                            <div class="grid aspect-4/3 overflow-hidden border-b-2 border-zinc-950 bg-orange-50">
                                @if ($wishlistItem->image_path)
                                    <img
                                        src="{{ Storage::disk('public')->url($wishlistItem->image_path) }}"
                                        alt="{{ $wishlistItem->name ?: 'Wishlist item' }}"
                                        class="size-full object-cover transition duration-300 group-hover:scale-[1.02] group-hover:saturate-110"
                                    />
                                @else
                                    <div class="grid place-items-center text-orange-300" aria-hidden="true">
                                        <flux:icon.photo class="size-12" />
                                    </div>
                                @endif
                            </div>

                            <div class="flex min-w-0 flex-1 flex-col p-4">
                                <div class="flex min-w-0 items-center justify-between gap-3">
                                    <h3 class="min-w-0 truncate text-lg leading-tight font-black tracking-tight">
                                        {{ $wishlistItem->name ?: 'Untitled item' }}
                                    </h3>

                                    @if ($wishlistItem->rating)
                                        <div
                                            class="flex shrink-0 items-center gap-1.5 text-xs font-black text-zinc-700"
                                            aria-label="Rated {{ $wishlistItem->rating }} out of 5"
                                        >
                                            <span class="text-orange-600" aria-hidden="true">★</span>
                                            <span>
                                                {{ (float) $wishlistItem->rating === (float) (int) $wishlistItem->rating ? Number::format($wishlistItem->rating) : Number::format($wishlistItem->rating, 1) }}
                                            </span>
                                            <span class="font-bold text-zinc-400">/ 5</span>
                                        </div>
                                    @endif
                                </div>

                                @if ($wishlistItem->notes)
                                    <p class="mt-2 line-clamp-3 text-sm leading-relaxed font-medium text-zinc-600">
                                        {{ $wishlistItem->notes }}
                                    </p>
                                @endif
                            </div>

                            @if ($wishlistItem->url)
                                <a
                                    href="{{ $wishlistItem->url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="relative z-20 border-t-2 border-zinc-950 bg-emerald-50 px-5 py-3 text-sm font-black text-emerald-700 hover:text-emerald-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-700"
                                >
                                    Open link ↗
                                </a>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>

        <flux:modal name="move-item-to-wishlist" class="max-w-md" wire:close="$set('wishlistSourceItemId', null)">
            <div class="grid gap-6">
                <div>
                    <flux:heading size="lg" class="font-black!">
                        Move to wishlist?
                    </flux:heading>

                    <flux:text class="mt-2! font-medium! text-zinc-600!">
                        Move {{ $wishlistSourceItemName ?: 'this item' }} to this collection’s private wishlist? It will no longer appear in the collection.
                    </flux:text>
                </div>

                <div class="flex gap-3 justify-end">
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

        <flux:modal name="move-wishlist-item-to-collection" class="max-w-md" wire:close="$set('collectionSourceWishlistItemId', null)">
            <div class="grid gap-6">
                <div>
                    <flux:heading size="lg" class="font-black!">
                        Move to collection?
                    </flux:heading>

                    <flux:text class="mt-2! font-medium! text-zinc-600!">
                        Move {{ $collectionSourceWishlistItemName ?: 'this item' }} to this collection? It will no longer appear on the wishlist.
                    </flux:text>
                </div>

                <div class="flex gap-3 justify-end">
                    <flux:modal.close>
                        <flux:button type="button" variant="ghost">Cancel</flux:button>
                    </flux:modal.close>

                    <flux:button
                        type="button"
                        variant="primary"
                        wire:click="moveToCollection"
                        wire:loading.attr="disabled"
                        wire:target="moveToCollection"
                    >
                        <span wire:loading.remove wire:target="moveToCollection">Move</span>
                        <span wire:loading wire:target="moveToCollection">Moving…</span>
                    </flux:button>
                </div>
            </div>
        </flux:modal>

        <flux:modal name="delete-collection-item-from-card" class="max-w-md" wire:close="$set('collectionItemPendingDeletionId', null)">
            <div class="grid gap-6">
                <div>
                    <flux:heading size="lg" class="font-black!">
                        Delete item?
                    </flux:heading>

                    <flux:text class="mt-2! font-medium! text-zinc-600!">
                        Permanently delete {{ $collectionItemPendingDeletionName ?: 'this item' }} and its photo? This cannot be undone.
                    </flux:text>
                </div>

                <div class="flex gap-3 justify-end">
                    <flux:modal.close>
                        <flux:button type="button" variant="ghost">Cancel</flux:button>
                    </flux:modal.close>

                    <flux:button
                        type="button"
                        variant="danger"
                        wire:click="deleteCollectionItem"
                        wire:loading.attr="disabled"
                        wire:target="deleteCollectionItem"
                    >
                        <span wire:loading.remove wire:target="deleteCollectionItem">Delete</span>
                        <span wire:loading wire:target="deleteCollectionItem">Deleting…</span>
                    </flux:button>
                </div>
            </div>
        </flux:modal>

        <flux:modal name="delete-wishlist-item-from-card" class="max-w-md" wire:close="$set('wishlistItemPendingDeletionId', null)">
            <div class="grid gap-6">
                <div>
                    <flux:heading size="lg" class="font-black!">
                        Delete wishlist item?
                    </flux:heading>

                    <flux:text class="mt-2! font-medium! text-zinc-600!">
                        Permanently delete {{ $wishlistItemPendingDeletionName ?: 'this item' }} from your wishlist? This cannot be undone.
                    </flux:text>
                </div>

                <div class="flex gap-3 justify-end">
                    <flux:modal.close>
                        <flux:button type="button" variant="ghost">Cancel</flux:button>
                    </flux:modal.close>

                    <flux:button
                        type="button"
                        variant="danger"
                        wire:click="deleteWishlistItem"
                        wire:loading.attr="disabled"
                        wire:target="deleteWishlistItem"
                    >
                        <span wire:loading.remove wire:target="deleteWishlistItem">Delete</span>
                        <span wire:loading wire:target="deleteWishlistItem">Deleting…</span>
                    </flux:button>
                </div>
            </div>
        </flux:modal>

        <livewire:collections.form :$collection />
        <livewire:collection-items.form :$collection />
        <livewire:wishlist-items.form :$collection />
    @endcan
</div>
