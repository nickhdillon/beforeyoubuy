<x-layouts::app title="Dashboard">
    <div class="w-full">
        <section class="flex flex-col gap-6 border-b-2 border-dashed border-emerald-200 pb-8 sm:flex-row sm:items-end sm:justify-between">
            <div class="max-w-2xl">
                <span class="hard-shadow inline-flex border-2 border-zinc-950 bg-orange-600 px-3 py-2 text-[11px] font-black tracking-[0.12em] text-white uppercase">
                    Your stuff, nicely sorted
                </span>
                <h1 class="mt-6 text-4xl leading-none font-black tracking-[-0.05em] sm:text-5xl">
                    Welcome back, {{ str(auth()->user()->name)->before(' ') }}.
                </h1>
                <p class="mt-4 max-w-xl text-base leading-relaxed font-medium text-zinc-600 sm:text-lg">
                    Keep track of what you own, organize it into collections, and know before you buy another one.
                </p>
            </div>

            <flux:button variant="primary" type="button" class="w-full sm:w-auto">
                New collection
            </flux:button>
        </section>

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
                    <p class="mt-3 text-4xl font-black tracking-[-0.04em]">0</p>
                    <p class="mt-2 text-sm font-medium text-zinc-600">Make a home for the things you own.</p>
                </article>

                <article class="hard-shadow border-2 border-zinc-950 bg-emerald-600 p-5 text-white">
                    <p class="text-xs font-black tracking-[0.12em] text-emerald-50 uppercase">Items tracked</p>
                    <p class="mt-3 text-4xl font-black tracking-[-0.04em]">0</p>
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
                    0 collections
                </span>
            </div>

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

                    <flux:button variant="secondary" type="button" class="w-full sm:w-auto">
                        Create collection
                    </flux:button>
                </div>
            </div>
        </section>
    </div>
</x-layouts::app>
