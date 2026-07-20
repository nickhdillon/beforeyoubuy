<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="auth-surface min-h-screen bg-zinc-50 text-zinc-950 antialiased selection:bg-orange-500 selection:text-zinc-950">
        <div class="relative isolate min-h-svh overflow-hidden">
            <div class="pointer-events-none absolute -top-28 -left-24 size-80 rounded-full bg-orange-400/35 blur-3xl"></div>
            <div class="pointer-events-none absolute right-[-8rem] bottom-[-5rem] size-96 rounded-full bg-emerald-200/35 blur-3xl"></div>

            <header class="relative z-20 mx-auto flex w-full max-w-7xl items-center justify-between px-4 py-5 sm:px-8 sm:py-6 lg:px-10">
                <a href="{{ route('home') }}" class="group flex items-center gap-3 font-black tracking-[-0.04em]" wire:navigate>
                    <span class="grid size-11 place-items-center border-2 border-zinc-950 bg-emerald-600 shadow-[4px_4px_0] shadow-zinc-950 transition group-hover:-translate-y-0.5 group-hover:shadow-[5px_5px_0] sm:size-12">
                        <svg class="size-8" viewBox="0 0 32 32" fill="none" aria-hidden="true" data-brand-mark>
                            <path d="M11 5h15v18H11z" class="fill-orange-600 stroke-zinc-950" stroke-width="2.5" />
                            <path d="M5 10h16v17H5z" class="fill-white stroke-zinc-950" stroke-width="2.5" />
                            <path d="M9 15h8M9 19h8M9 23h5" class="stroke-emerald-700" stroke-width="2.5" />
                        </svg>
                    </span>
                    <span class="hidden flex-col text-[0.82rem] leading-[0.82] font-black tracking-[-0.045em] uppercase sm:flex sm:text-[0.9rem]">
                        <span>Before</span>
                        <span class="text-emerald-700">You Buy<span class="ml-1 inline-block size-1.5 bg-orange-600" aria-hidden="true"></span></span>
                    </span>
                </a>

                <a href="{{ route('home') }}" class="text-xs font-bold text-zinc-700/60 transition hover:text-emerald-700 sm:text-sm" wire:navigate>
                    ← Back home
                </a>
            </header>

            <main class="relative z-10 flex min-h-[calc(100svh-10rem)] items-center justify-center px-4 py-5 sm:min-h-[calc(100svh-5.5rem)] sm:px-6 sm:py-14">
                <div class="w-full max-w-md border-[3px] border-zinc-950 bg-white p-5 shadow-none sm:p-8 sm:shadow-[10px_12px_0] sm:shadow-zinc-950">
                    <div class="flex flex-col gap-6">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>

        @persist('toast')
            <flux:toast.group>
                <flux:toast />
            </flux:toast.group>
        @endpersist

        @fluxScripts
    </body>
</html>
