<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Keep track of what you and the people you care about already own before buying something twice.">

        <title>Shop smarter - Before You Buy</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-zinc-50 font-sans text-zinc-950 antialiased selection:bg-orange-500 selection:text-zinc-950">
        <div class="relative isolate min-h-screen overflow-hidden">
            <div class="pointer-events-none absolute -top-24 -left-24 size-72 rounded-full bg-orange-400/35 blur-3xl"></div>
            <div class="pointer-events-none absolute top-1/3 -right-32 size-96 rounded-full bg-emerald-200/35 blur-3xl"></div>

            <header class="relative z-20 mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-5 sm:px-8 sm:py-6 lg:px-10">
                <a href="{{ route('home') }}" class="group flex items-center gap-3" aria-label="Before You Buy home">
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

                @if (Route::has('login'))
                    <nav class="flex shrink-0 items-center gap-1 sm:gap-3" aria-label="Account navigation">
                        @auth
                            <a href="{{ route('dashboard') }}" class="border-2 border-zinc-950 bg-white px-3 py-2 text-xs font-bold shadow-[3px_3px_0] shadow-zinc-950 transition hover:-translate-y-0.5 hover:shadow-[5px_5px_0] sm:px-5 sm:text-sm">My dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="px-2 py-2 text-xs font-bold transition hover:text-emerald-700 sm:px-4 sm:text-sm">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="border-2 border-zinc-950 bg-orange-600 px-3 py-2 text-xs font-bold text-white shadow-[3px_3px_0] shadow-zinc-950 transition hover:-translate-y-0.5 hover:bg-orange-500 hover:shadow-[5px_5px_0] sm:px-5 sm:text-sm">Join free</a>
                            @endif
                        @endauth
                    </nav>
                @endif
            </header>

            <main class="relative z-10 mx-auto grid max-w-7xl items-center gap-16 px-4 pt-8 pb-20 sm:px-8 sm:pt-16 lg:grid-cols-[1.05fr_.95fr] lg:gap-8 lg:px-10 lg:pt-20">
                <section class="max-w-2xl">
                    <div class="motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-3 motion-safe:duration-500">
                        <span class="inline-flex items-center gap-2 border-2 border-zinc-950 bg-orange-600 px-3 py-2 text-[11px] font-black tracking-[0.1em] text-white uppercase shadow-[3px_3px_0] shadow-zinc-950 sm:px-4 sm:text-xs sm:tracking-[0.13em]">
                            Your stuff, nicely sorted
                        </span>
                    </div>

                    <h1 class="mt-7 text-[2.75rem] leading-[0.94] font-black tracking-[-0.06em] text-balance motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-4 motion-safe:fill-mode-both motion-safe:delay-150 motion-safe:duration-700 sm:mt-8 sm:text-7xl lg:text-[5.75rem]">
                        Know what you <span class="relative inline-block text-emerald-700"><span class="relative z-10">have</span><span class="absolute -bottom-1 left-0 h-2 w-full bg-orange-600" aria-hidden="true"></span></span> before you buy.
                    </h1>

                    <p class="mt-6 max-w-xl text-base leading-relaxed font-medium text-zinc-700/70 motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-4 motion-safe:fill-mode-both motion-safe:delay-300 motion-safe:duration-700 sm:mt-8 sm:text-xl">
                        Keep tabs on your favorite things, from coffee gear to playing cards. Add what you own, organize it your way, and share collections with friends and family.
                    </p>

                    <div class="mt-8 flex flex-col gap-4 motion-safe:animate-in motion-safe:fade-in motion-safe:slide-in-from-bottom-4 motion-safe:fill-mode-both motion-safe:delay-500 motion-safe:duration-700 sm:mt-10 sm:flex-row sm:items-center">
                        @auth
                            <a href="{{ route('dashboard') }}" class="group inline-flex w-full shrink-0 items-center justify-center gap-3 whitespace-nowrap border-2 border-zinc-950 bg-emerald-600 px-6 py-3.5 font-black text-white shadow-[5px_6px_0] shadow-zinc-950 transition hover:-translate-y-1 hover:shadow-[7px_9px_0] sm:w-auto sm:px-7 sm:py-4">
                                View your collections
                                <span class="transition group-hover:translate-x-1" aria-hidden="true">→</span>
                            </a>
                        @elseif (Route::has('register'))
                            <a href="{{ route('register') }}" class="group inline-flex w-full items-center justify-center gap-3 border-2 border-zinc-950 bg-emerald-600 px-6 py-3.5 font-black text-white shadow-[5px_6px_0] shadow-zinc-950 transition hover:-translate-y-1 hover:shadow-[7px_9px_0] sm:w-auto sm:px-7 sm:py-4">
                                Start a collection
                                <span class="transition group-hover:translate-x-1" aria-hidden="true">→</span>
                            </a>
                        @endif
                        <span class="px-3 text-center text-xs font-bold text-zinc-700/60 sm:min-w-0 sm:flex-1 sm:px-0 sm:text-left sm:text-sm">Start with one collection. Add the rest as you go.</span>
                    </div>
                </section>

                <section class="relative mx-auto w-[calc(100%-0.75rem)] max-w-xl pt-5 motion-safe:animate-in motion-safe:zoom-in-95 motion-safe:fade-in motion-safe:fill-mode-both motion-safe:delay-300 motion-safe:duration-700 sm:w-full sm:pt-0" aria-label="Owned items collection preview">
                    <div class="border-[3px] border-zinc-950 bg-emerald-600 p-3 shadow-[8px_9px_0] shadow-zinc-950 sm:p-6 sm:shadow-[10px_12px_0]">
                        <div class="border-2 border-zinc-950 bg-white p-4 sm:p-7">
                            <div class="flex items-center justify-between gap-3 border-b-2 border-dashed border-emerald-200 pb-3 sm:gap-4 sm:pb-2">
                                <h2 class="text-xl font-black tracking-tight sm:text-2xl">Coffee Gear</h2>
                                <p class="mt-0.5 text-[10px] font-black tracking-wider text-emerald-700 uppercase sm:text-xs sm:tracking-widest">2 items</p>
                            </div>

                            <div class="mt-3 grid grid-cols-2 gap-2.5 sm:mt-4 sm:gap-4">
                                <article class="group border-2 border-zinc-950 bg-zinc-50 p-2.5 transition hover:-translate-y-1 sm:p-4">
                                    <div class="grid aspect-[4/3] place-items-center bg-orange-600 text-white transition group-hover:scale-[1.03]">
                                        <svg class="size-20 sm:size-28" viewBox="0 0 120 100" fill="none" role="img" aria-label="French press">
                                            <path d="M40 28h44v58H40z" class="fill-white/70 stroke-current" stroke-width="4" />
                                            <path d="M44 61h36v21H44z" class="fill-emerald-500/70" />
                                            <path d="M36 24h52M62 10v18M53 10h18" class="stroke-current" stroke-width="4" stroke-linecap="square" />
                                            <path d="M84 40h10c9 0 13 8 13 17v8c0 9-5 14-13 14H84" class="stroke-current" stroke-width="4" />
                                            <path d="M35 88h54" class="stroke-current" stroke-width="4" stroke-linecap="square" />
                                        </svg>
                                    </div>
                                    <div class="mt-3 flex items-center justify-between gap-1 sm:mt-4 sm:gap-3">
                                        <h3 class="text-sm leading-tight font-black sm:text-base">French press</h3>
                                        <span class="bg-white px-1.5 py-0.5 text-xs font-black sm:px-2.5 sm:py-1 sm:text-sm">1</span>
                                    </div>
                                </article>

                                <article class="group border-2 border-zinc-950 bg-zinc-50 p-2.5 transition hover:-translate-y-1 sm:p-4">
                                    <div class="grid aspect-[4/3] place-items-center bg-emerald-200 text-zinc-950 transition group-hover:scale-[1.03]">
                                        <svg class="size-20 sm:size-28" viewBox="0 0 120 100" fill="none" role="img" aria-label="Pour-over coffee maker">
                                            <path d="M35 18h50L75 49H45z" class="fill-white/80 stroke-current" stroke-width="4" stroke-linejoin="miter" />
                                            <path d="M30 52h60" class="stroke-current" stroke-width="4" stroke-linecap="square" />
                                            <path d="M42 52h36l7 34H35z" class="fill-white/70 stroke-current" stroke-width="4" stroke-linejoin="miter" />
                                            <path d="M40 70h40l3 16H37z" class="fill-orange-500/70" />
                                            <path d="M86 62h5c9 0 14 5 14 12s-5 12-14 12h-6" class="stroke-current" stroke-width="4" />
                                            <path d="M54 30c5 4 11 4 16 0" class="stroke-current" stroke-width="3" />
                                        </svg>
                                    </div>
                                    <div class="mt-3 flex items-center justify-between gap-1 sm:mt-4 sm:gap-3">
                                        <h3 class="text-sm leading-tight font-black sm:text-base">Pour over</h3>
                                        <span class="bg-white px-1.5 py-0.5 text-xs font-black sm:px-2.5 sm:py-1 sm:text-sm">1</span>
                                    </div>
                                </article>
                            </div>

                            <div class="mt-4 flex items-center gap-2.5 bg-zinc-950 p-3 text-white sm:mt-5 sm:gap-3 sm:p-4">
                                <span class="grid size-8 shrink-0 place-items-center bg-orange-600 text-white sm:size-9">✓</span>
                                <p class="text-xs font-bold sm:text-sm"><span class="text-orange-600">Added to collection:</span> Pour over is now in Coffee Gear.</p>
                            </div>
                        </div>
                    </div>
                    <div class="absolute -bottom-6 -left-1 border-2 border-zinc-950 bg-orange-600 px-3 py-2 text-xs font-black text-white shadow-[4px_4px_0] shadow-zinc-950 sm:-bottom-7 sm:-left-8 sm:px-4 sm:py-3 sm:text-sm">2 items and counting</div>
                </section>
            </main>

            <footer class="relative z-10 mx-auto flex max-w-7xl flex-col gap-2 px-5 pb-8 text-center text-xs font-semibold text-zinc-700/50 sm:px-8 lg:px-10">
                <p>Organize what you own and share collections with friends and family.</p>
            </footer>
        </div>
    </body>
</html>
