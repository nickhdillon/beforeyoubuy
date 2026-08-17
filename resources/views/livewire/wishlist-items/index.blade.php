<div>
    <section class="mt-12 border-t-2 border-dashed border-emerald-200 pt-8" aria-labelledby="wishlist-heading">
        <div class="flex flex-col">
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
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

            <p class="mt-4 max-w-xl text-sm leading-relaxed font-medium text-zinc-600 sm:mt-2">
                Keep track of what you might buy next. Your wishlist is visible only to you, even when this collection is public.
            </p>
        </div>

        @if ($this->items->isEmpty())
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
                @foreach ($this->items as $item)
                    <article wire:key="wishlist-item-{{ $item->id }}" class="hard-shadow hard-shadow-hover group relative flex min-w-0 flex-col border-2 border-zinc-950 bg-white transition hover:-translate-y-0.5">
                        <button
                            type="button"
                            class="absolute inset-0 z-10 cursor-pointer focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-orange-600"
                            x-on:click="$dispatch('edit-wishlist-item', { itemId: {{ $item->id }} })"
                            aria-label="Edit {{ $item->name ?: 'untitled wishlist item' }}"
                        ></button>

                        <div class="absolute top-3 end-3 z-20">
                            <flux:dropdown position="bottom" align="end">
                                <flux:button
                                    square
                                    size="sm"
                                    variant="secondary"
                                    icon="ellipsis-horizontal"
                                    aria-label="Actions for {{ $item->name ?: 'untitled wishlist item' }}"
                                    class="hard-shadow px-4!"
                                />

                                <flux:menu class="hard-shadow min-w-48 rounded-none! border-2! border-zinc-950! bg-white! p-1!">
                                    <flux:menu.item
                                        class="rounded-none! px-2.5! py-2! font-black! text-zinc-950! data-active:bg-orange-100/65!"
                                        x-on:click="$dispatch('edit-wishlist-item', { itemId: {{ $item->id }} })"
                                    >
                                        Edit item
                                    </flux:menu.item>

                                    <flux:menu.item
                                        class="rounded-none! px-2.5! py-2! font-black! text-zinc-950! data-active:bg-orange-100/65!"
                                        wire:click="confirmMoveToCollection({{ $item->id }})"
                                    >
                                        Move to collection
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

                        @if ($item->quantity > 1)
                            <span
                                class="hard-shadow absolute top-3 start-3 z-20 border-2 border-zinc-950 bg-white px-2.5 py-1 text-xs font-black"
                                aria-label="Quantity {{ $item->quantity }}"
                            >
                                ×{{ Number::format($item->quantity) }}
                            </span>
                        @endif

                        <div class="grid aspect-4/3 place-items-center overflow-hidden border-b-2 border-zinc-950 bg-orange-50">
                            @if ($item->image_path)
                                <img
                                    src="{{ Storage::disk('public')->url($item->image_path) }}"
                                    alt="{{ $item->name ?: 'Wishlist item' }}"
                                    class="block size-full object-cover object-center transition duration-300 group-hover:scale-[1.02] group-hover:saturate-110"
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
                                <p class="mt-2 line-clamp-3 text-sm leading-relaxed font-medium text-zinc-600">
                                    {{ $item->notes }}
                                </p>
                            @endif
                        </div>

                        @if ($item->url)
                            <a
                                href="{{ $item->url }}"
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

    <flux:modal name="move-wishlist-item-to-collection" class="max-w-md" wire:close="$set('pendingItem', null)">
        <div class="grid gap-6">
            <div>
                <flux:heading size="lg" class="font-black!">
                    Move to collection?
                </flux:heading>

                <flux:text class="mt-2! font-medium! text-zinc-600!">
                    Move {{ $pendingItem?->name ?: 'this item' }} to this collection? It will no longer appear on the wishlist.
                </flux:text>
            </div>

            <div class="flex justify-end gap-3">
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

    <flux:modal name="delete-wishlist-item-from-card" class="max-w-md" wire:close="$set('pendingItem', null)">
        <div class="grid gap-6">
            <div>
                <flux:heading size="lg" class="font-black!">
                    Delete wishlist item?
                </flux:heading>

                <flux:text class="mt-2! font-medium! text-zinc-600!">
                    Permanently delete {{ $pendingItem?->name ?: 'this item' }} from your wishlist? This cannot be undone.
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

    <livewire:wishlist-items.form :$collection />
</div>
