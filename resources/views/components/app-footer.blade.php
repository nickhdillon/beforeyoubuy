<footer class="relative z-10 border-t-2 border-zinc-950 bg-white" data-test="app-footer">
    <div class="mx-auto flex w-full max-w-7xl flex-col gap-5 px-4 py-6 sm:px-8 md:flex-row md:items-center md:justify-between lg:px-10">
        <div class="flex flex-wrap items-center gap-x-3 gap-y-2 text-sm font-bold text-zinc-600">
            <div class="flex items-center gap-1.5">
                <a href="{{ route('home') }}" class="group flex items-center gap-2 text-zinc-950" wire:navigate>
                    <span class="grid size-7 place-items-center border-2 border-zinc-950 bg-emerald-600 transition group-hover:-translate-y-0.5" aria-hidden="true">
                        <svg class="size-5" viewBox="0 0 32 32" fill="none">
                            <path d="M11 5h15v18H11z" class="fill-orange-600 stroke-zinc-950" stroke-width="2.5" />
                            <path d="M5 10h16v17H5z" class="fill-white stroke-zinc-950" stroke-width="2.5" />
                            <path d="M9 15h8M9 19h8M9 23h5" class="stroke-emerald-700" stroke-width="2.5" />
                        </svg>
                    </span>
                    <span>Before You Buy</span>
                </a>

                <span aria-hidden="true">©</span>
                <span>{{ now()->year }}</span>
            </div>

            <span class="text-zinc-400" aria-hidden="true">·</span>
            <span class="text-zinc-500">Buy thoughtfully.</span>
        </div>

        <nav class="flex flex-wrap items-center gap-x-5 gap-y-2 text-sm font-bold text-zinc-600" aria-label="Footer navigation">
            <a href="{{ route('home') }}" class="transition hover:text-emerald-700" wire:navigate>Home</a>
            @auth
                <a href="{{ route('dashboard') }}" class="transition hover:text-emerald-700" wire:navigate>Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="transition hover:text-emerald-700" wire:navigate>Log in</a>
            @endauth
            <a href="https://github.com/nickhdillon/beforeyoubuy" class="transition hover:text-emerald-700" target="_blank" rel="noreferrer">GitHub</a>
        </nav>
    </div>
</footer>
