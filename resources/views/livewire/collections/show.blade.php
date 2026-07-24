<div class="w-full">
    <a href="{{ auth()->check() ? route('collections.index') : route('home') }}" class="inline-flex items-center gap-2 text-sm font-black text-emerald-700 hover:text-emerald-900" wire:navigate>← {{ auth()->check() ? 'Back to collections' : 'Before You Buy' }}</a>

    <header class="mt-6 flex flex-col gap-4 border-b-2 border-dashed border-emerald-200 pb-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="min-w-0">
                <div class="mt-2 flex w-fit max-w-full items-center gap-4">
                    <h1 class="min-w-0 text-4xl leading-none font-black tracking-[-0.05em] break-words sm:text-5xl">{{ $collection->name }}</h1>

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
            <p class="max-w-2xl text-base leading-relaxed font-medium text-zinc-600 sm:text-lg">{{ $collection->description }}</p>
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
                            Nothing photographed yet
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
                    @can('update', $item)
                        <button
                            type="button"
                            wire:key="collection-item-{{ $item->id }}"
                            x-on:click="$dispatch('edit-collection-item', { itemId: {{ $item->id }} })"
                            aria-label="Edit {{ $item->name ?: 'untitled item' }}"
                            class="hard-shadow hard-shadow-hover group flex cursor-pointer flex-col border-2 border-zinc-950 bg-white text-left transition hover:-translate-y-0.5 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-700"
                        >
                    @else
                        <article
                            wire:key="collection-item-{{ $item->id }}"
                            class="hard-shadow flex flex-col border-2 border-zinc-950 bg-white"
                        >
                    @endcan
                        <img
                            src="{{ Storage::disk('public')->url($item->image_path) }}"
                            alt="{{ $item->name ?: 'Collection item' }}"
                            class="aspect-4/3 w-full object-cover transition duration-300 group-hover:saturate-110"
                        />

                        <div class="grid grid-cols-[1fr_auto] items-center gap-3 border-y-2 border-zinc-950 bg-emerald-50 px-4 py-2">
                            <div class="flex items-center gap-3 text-sm font-black" @if ($item->rating) aria-label="Rated {{ $item->rating }} out of 5" @endif>
                                @if ($item->rating)
                                    <span><span class="text-orange-600" aria-hidden="true">★</span> {{ (float) $item->rating === (float) (int) $item->rating ? Number::format($item->rating) : Number::format($item->rating, 1) }}</span>
                                @else
                                    <span class="text-zinc-500">Not rated</span>
                                @endif
                            </div>

                            <span class="text-sm font-black">{{ Number::format($item->quantity) }}</span>
                        </div>

                        <div class="flex flex-1 flex-col px-4 py-2">
                            <h3 class="truncate text-lg font-black tracking-tight">{{ $item->name ?: 'Untitled item' }}</h3>

                            @if ($item->notes)
                                <p class="mt-3 line-clamp-2 text-sm leading-relaxed font-medium text-zinc-600">{{ $item->notes }}</p>
                            @endif
                        </div>
                    @can('update', $item)
                        </button>
                    @else
                        </article>
                    @endcan
                @endforeach
            </div>
        @endif
    </section>

    @can('update', $collection)
        <livewire:collections.form :$collection />
        <livewire:collection-items.form :$collection />
    @endcan
</div>
