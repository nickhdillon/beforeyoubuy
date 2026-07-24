<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-950 antialiased selection:bg-orange-500 selection:text-white">
        <header
            x-data="{ mobileNavigationOpen: false }"
            x-on:keydown.escape.window="mobileNavigationOpen = false"
            class="relative z-30 border-b-2 border-zinc-950 bg-white"
            data-test="app-header"
        >
            <div class="mx-auto flex min-h-18 w-full max-w-7xl items-center gap-4 px-4 py-3 sm:px-8 lg:px-10">
                <x-app-logo href="{{ route('dashboard') }}" wire:navigate />

                <nav class="ms-5 hidden items-center gap-2 md:flex" aria-label="Main navigation">
                    <a
                        href="{{ route('dashboard') }}"
                        @class([
                            'relative px-2 py-2 text-sm font-black transition after:absolute after:inset-x-2 after:-bottom-1 after:h-1 after:transition',
                            'text-zinc-950 after:bg-emerald-600' => request()->routeIs('dashboard'),
                            'text-zinc-600 after:scale-x-0 after:bg-zinc-300 hover:text-zinc-950 hover:after:scale-x-100' => ! request()->routeIs('dashboard'),
                        ])
                        wire:navigate
                    >
                        Dashboard
                    </a>
                    
                    <a
                        href="{{ route('collections.index') }}"
                        @class([
                            'relative px-2 py-2 text-sm font-black transition after:absolute after:inset-x-2 after:-bottom-1 after:h-1 after:transition',
                            'text-zinc-950 after:bg-emerald-600' => request()->routeIs('collections.*'),
                            'text-zinc-600 after:scale-x-0 after:bg-zinc-300 hover:text-zinc-950 hover:after:scale-x-100' => ! request()->routeIs('collections.*'),
                        ])
                        wire:navigate
                    >
                        Collections
                    </a>
                </nav>

                <div class="flex-1"></div>

                @auth
                    <x-desktop-user-menu />

                    <button
                        type="button"
                        class="hard-shadow hard-shadow-hover flex items-center gap-2 border-2 border-zinc-950 bg-white p-1.5 hover:bg-emerald-50 md:hidden"
                        x-on:click="mobileNavigationOpen = ! mobileNavigationOpen"
                        x-bind:aria-expanded="mobileNavigationOpen"
                        aria-controls="mobile-navigation"
                        data-test="mobile-navigation-button"
                    >
                        <span class="grid size-8 place-items-center bg-orange-600 text-xs font-black text-white">
                            {{ auth()->user()->initials() }}
                        </span>
                        <flux:icon.chevron-down class="size-4 transition" x-bind:class="mobileNavigationOpen && 'rotate-180'" />
                        <span class="sr-only">Open account navigation</span>
                    </button>
                @else
                    <flux:button :href="route('login')" wire:navigate>Log in</flux:button>
                @endauth
            </div>

            @auth
                <div
                    id="mobile-navigation"
                    x-cloak
                    x-show="mobileNavigationOpen"
                    x-collapse
                    x-on:click.outside="mobileNavigationOpen = false"
                    class="hard-shadow absolute inset-x-0 top-full w-full bg-zinc-50 md:hidden"
                    data-test="mobile-navigation"
                >
                    <div class="mx-auto grid w-full max-w-7xl gap-3 px-4 py-4 sm:px-8">
                        <div class="flex items-center gap-3 border-2 border-zinc-950 bg-emerald-600 p-3 text-white">
                            <span class="grid size-10 shrink-0 place-items-center bg-orange-600 text-sm font-black">
                                {{ auth()->user()->initials() }}
                            </span>
                            <div class="min-w-0">
                                <p class="truncate text-sm font-black">{{ auth()->user()->name }}</p>
                                <p class="truncate text-xs font-medium text-emerald-50">{{ auth()->user()->email }}</p>
                            </div>
                        </div>

                        <nav class="grid gap-2" aria-label="Mobile navigation">
                            <a
                                href="{{ route('dashboard') }}"
                                class="hard-shadow hard-shadow-hover flex w-full items-center gap-3 border-2 border-zinc-950 bg-white px-4 py-3 text-sm font-black hover:-translate-y-0.5 hover:bg-emerald-50"
                                x-on:click="mobileNavigationOpen = false"
                                wire:navigate
                            >
                                <flux:icon.layout-grid class="size-5 text-emerald-700" />
                                Dashboard
                            </a>

                            <a
                                href="{{ route('collections.index') }}"
                                class="hard-shadow hard-shadow-hover flex w-full items-center gap-3 border-2 border-zinc-950 bg-white px-4 py-3 text-sm font-black hover:-translate-y-0.5 hover:bg-emerald-50"
                                x-on:click="mobileNavigationOpen = false"
                                wire:navigate
                            >
                                <flux:icon.layout-grid class="size-5 text-emerald-700" />
                                Collections
                            </a>

                            <a
                                href="{{ route('profile.edit') }}"
                                class="hard-shadow hard-shadow-hover flex w-full items-center gap-3 border-2 border-zinc-950 bg-white px-4 py-3 text-sm font-black hover:-translate-y-0.5 hover:bg-emerald-50"
                                x-on:click="mobileNavigationOpen = false"
                                wire:navigate
                            >
                                <flux:icon.cog class="size-5 text-emerald-700" />
                                Settings
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="hard-shadow hard-shadow-hover flex w-full items-center gap-3 border-2 border-zinc-950 bg-orange-600 px-4 py-3 text-sm font-black text-white hover:-translate-y-0.5 hover:bg-orange-500"
                                    data-test="mobile-logout-button"
                                >
                                    <flux:icon.arrow-right-start-on-rectangle class="size-5" />
                                    Log out
                                </button>
                            </form>
                        </nav>
                    </div>
                </div>
            @endauth
        </header>

        <main class="mx-auto flex w-full max-w-7xl px-4 py-8 sm:px-8 sm:py-10 lg:px-10 lg:py-12">
            {{ $slot }}
        </main>

        @fluxScripts
    </body>
</html>
