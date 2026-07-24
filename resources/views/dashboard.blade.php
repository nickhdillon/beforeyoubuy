<x-layouts::app title="Dashboard">
    <div class="w-full" x-on:collection-created.window="window.location.reload()">
        <div class="flex flex-col gap-4 border-b-2 border-dashed border-emerald-200 pb-8">
            <span class="hard-shadow inline-flex border-2 border-zinc-950 bg-orange-600 px-3 py-2 text-[11px] font-black tracking-[0.12em] text-white uppercase w-fit">
                Your stuff, nicely sorted
            </span>

            <section class="flex mt-2 flex-col gap-4 sm:flex-row sm:items-center justify-between">
                <h1 class="text-4xl leading-none font-black tracking-[-0.05em] sm:text-5xl">
                    Welcome back, {{ str(auth()->user()->name)->before(' ') }}.
                </h1>
                
                <flux:modal.trigger name="collection-form">
                    <flux:button variant="primary" class="w-full sm:w-auto">
                        New collection
                    </flux:button>
                </flux:modal.trigger>
            </section>

            <p class="max-w-xl text-base leading-relaxed font-medium text-zinc-600 sm:text-lg">
                Keep track of what you own, organize it into collections, and know before you buy another one.
            </p>
        </div>

        <section class="mt-8" aria-labelledby="overview-heading">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs font-black tracking-[0.12em] text-emerald-700 uppercase">At a glance</p>
                    <h2 id="overview-heading" class="mt-1 text-2xl font-black tracking-tight">Your overview</h2>
                </div>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-3">
                <article class="hard-shadow border-2 border-zinc-950 bg-white p-5">
                    <p class="text-xs font-black tracking-[0.12em] text-zinc-500 uppercase">Collections</p>
                    <p class="mt-3 text-4xl font-black tracking-[-0.04em]">{{ Number::format($collections->count()) }}</p>
                    <p class="mt-2 text-sm font-medium text-zinc-600">Make a home for the things you own.</p>
                </article>

                <article class="hard-shadow border-2 border-zinc-950 bg-emerald-600 p-5 text-white">
                    <p class="text-xs font-black tracking-[0.12em] text-emerald-50 uppercase">Items tracked</p>
                    <p class="mt-3 text-4xl font-black tracking-[-0.04em]">{{ Number::format($itemsCount) }}</p>
                    <p class="mt-2 text-sm font-medium text-emerald-50">Everything you add will count toward this total.</p>
                </article>

                <article class="hard-shadow border-2 border-zinc-950 bg-orange-600 p-5 text-white">
                    <p class="text-xs font-black tracking-[0.12em] text-orange-50 uppercase">Shared with you</p>
                    <p class="mt-3 text-4xl font-black tracking-[-0.04em]">0</p>
                    <p class="mt-2 text-sm font-medium text-orange-50">Collections from friends and family will appear here.</p>
                </article>
            </div>
        </section>

        <section class="mt-10" aria-labelledby="collections-heading">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-black tracking-[0.12em] text-emerald-700 uppercase">All together now</p>
                    <h2 id="collections-heading" class="mt-1 text-2xl font-black tracking-tight">Your collections</h2>
                </div>
                <span class="hard-shadow border-2 border-zinc-950 bg-white px-3 py-1 text-xs font-black">
                    {{ $collections->count() }} {{ str('collection')->plural($collections->count()) }}
                </span>
            </div>

            @if ($collections->isEmpty())
                <div class="hard-shadow mt-5 border-2 border-zinc-950 bg-white p-4 sm:p-8">
                    <div class="grid gap-6 border-2 border-dashed border-emerald-300 bg-emerald-50 p-6 sm:grid-cols-[auto_1fr_auto] sm:items-center sm:p-8">
                        <div class="hard-shadow grid size-16 place-items-center border-2 border-zinc-950 bg-emerald-600" aria-hidden="true">
                            <svg class="size-10" viewBox="0 0 32 32" fill="none">
                                <path d="M11 5h15v18H11z" class="fill-orange-600 stroke-zinc-950" stroke-width="2.5" />
                                <path d="M5 10h16v17H5z" class="fill-white stroke-zinc-950" stroke-width="2.5" />
                                <path d="M9 15h8M9 19h8M9 23h5" class="stroke-emerald-700" stroke-width="2.5" />
                            </svg>
                        </div>

                        <div>
                            <h3 class="text-xl font-black tracking-tight">Start with one collection</h3>
                            <p class="mt-2 max-w-xl text-sm leading-relaxed font-medium text-zinc-600">
                                Try coffee gear, books, games, or anything else you want to remember before your next purchase.
                            </p>
                        </div>

                        <flux:modal.trigger name="collection-form">
                            <flux:button variant="secondary" class="w-full sm:w-auto">
                                Create collection
                            </flux:button>
                        </flux:modal.trigger>
                    </div>
                </div>
            @else
                <div class="mt-5 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($collections as $collection)
                        <a
                            href="{{ route('collections.show', $collection) }}"
                            wire:navigate
                            class="hard-shadow hard-shadow-hover group flex min-h-40 flex-col border-2 border-zinc-950 bg-white p-5 transition hover:-translate-y-0.5 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-emerald-700"
                        >
                            <div class="flex items-center justify-between gap-3">
                                <h3 class="truncate text-xl font-black tracking-tight">{{ $collection->name }}</h3>
                                <span @class(['shrink-0 px-2.5 py-1 text-[11px] font-black uppercase tracking-wide', 'bg-emerald-600 text-white' => $collection->is_public, 'bg-zinc-200 text-zinc-700' => ! $collection->is_public])>
                                    {{ $collection->is_public ? 'Public' : 'Private' }}
                                </span>
                            </div>

                            @if ($collection->description)
                                <p class="mt-2 line-clamp-2 text-sm leading-relaxed font-medium text-zinc-600">{{ $collection->description }}</p>
                            @endif

                            <div class="mt-auto flex items-end justify-between gap-3 border-t-2 border-dashed border-emerald-200 pt-4">
                                <p class="text-sm font-medium text-zinc-500">
                                    {{ $collection->items_count }} {{ str('item')->plural($collection->items_count) }}
                                </p>
                                <span class="text-sm font-black text-emerald-700 group-hover:text-emerald-900">Open →</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        <livewire:collections.form />
    </div>
</x-layouts::app>
