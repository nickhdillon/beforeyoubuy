<div class="w-full">
    <a
        href="{{ auth()->check() ? route('collections.index') : route('home') }}"
        class="inline-flex items-center gap-2 text-sm font-black text-emerald-700 hover:text-emerald-900"
        wire:navigate
    >
        ← {{ auth()->check() ? 'Back to collections' : 'Before You Buy' }}
    </a>

    <header class="mt-6 flex flex-col gap-4 border-b-2 border-dashed border-emerald-200 pb-8">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
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
                                        navigator.clipboard.writeText(@js(route('collections.public', ['user' => $collection->user, 'collection' => $collection]))).then(() => {
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

        <p class="text-xs font-bold text-zinc-500">
            <time
                datetime="{{ $this->lastUpdatedAt->toAtomString() }}"
                title="Last updated {{ $this->lastUpdatedAt->format('F j, Y \a\t g:i A') }}"
            >
                Updated {{ $this->lastUpdatedAt->diffForHumans() }}
            </time>
        </p>
    </header>

    <livewire:collection-items.index :$collection :public-view="request()->routeIs('collections.public')" />

    @can('update', $collection)
        <livewire:wishlist-items.index :$collection :wishlist="$collection->wishlist" />
        <livewire:collections.form :$collection />
    @endcan
</div>
