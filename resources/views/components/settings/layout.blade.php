@props([
    'current',
])

<div class="grid items-start gap-6 md:grid-cols-[220px_minmax(0,1fr)] lg:gap-8">
    <aside class="hard-shadow border-2 border-zinc-950 bg-white p-3" aria-label="Settings navigation">
        <p class="px-3 pb-3 text-[11px] font-black tracking-[0.12em] text-emerald-700 uppercase">Account settings</p>

        <nav class="grid grid-cols-2 gap-2 md:grid-cols-1">
            <a
                href="{{ route('profile.edit') }}"
                @class([
                    'relative border-2 px-4 py-3 text-sm font-black transition-colors after:absolute after:inset-y-0 after:left-0 after:w-1 after:bg-orange-600 after:transition-transform',
                    'border-zinc-950 bg-emerald-600 text-white after:scale-y-100' => $current === 'profile',
                    'border-transparent text-zinc-600 after:scale-y-0 hover:border-zinc-300 hover:bg-emerald-50 hover:text-zinc-950' => $current !== 'profile',
                ])
                wire:navigate
            >
                Profile
            </a>
            <a
                href="{{ route('security.edit') }}"
                @class([
                    'relative border-2 px-4 py-3 text-sm font-black transition-colors after:absolute after:inset-y-0 after:left-0 after:w-1 after:bg-orange-600 after:transition-transform',
                    'border-zinc-950 bg-emerald-600 text-white after:scale-y-100' => $current === 'security',
                    'border-transparent text-zinc-600 after:scale-y-0 hover:border-zinc-300 hover:bg-emerald-50 hover:text-zinc-950' => $current !== 'security',
                ])
                wire:navigate
            >
                Security
            </a>
        </nav>
    </aside>

    <section class="hard-shadow border-2 border-zinc-950 bg-white p-5 sm:p-7 lg:p-8">
        <header class="border-b-2 border-dashed border-emerald-200 pb-5">
            <p class="text-xs font-black tracking-[0.12em] text-emerald-700 uppercase">Settings</p>
            <h2 class="mt-1 text-2xl font-black tracking-tight">{{ $heading ?? '' }}</h2>
            <p class="mt-2 font-medium text-zinc-600">{{ $subheading ?? '' }}</p>
        </header>

        <div class="mt-6 w-full max-w-xl">
            {{ $slot }}
        </div>
    </section>
</div>
