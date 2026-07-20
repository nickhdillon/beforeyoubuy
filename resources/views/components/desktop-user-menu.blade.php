<flux:dropdown position="bottom" align="end" :gap="10">
    <button
        type="button"
        class="hard-shadow hard-shadow-hover group hidden items-center gap-3 border-2 border-zinc-950 bg-white px-3 py-2 text-start hover:-translate-y-0.5 hover:bg-emerald-50 md:flex"
        data-test="user-menu-button"
    >
        <span class="grid size-8 shrink-0 place-items-center bg-orange-600 text-xs font-black text-white">
            {{ auth()->user()->initials() }}
        </span>
        <span class="hidden min-w-0 lg:block">
            <span class="block max-w-36 truncate text-sm font-black">{{ auth()->user()->name }}</span>
            <span class="block text-[10px] font-bold tracking-[0.12em] text-emerald-700 uppercase">Your account</span>
        </span>
        <flux:icon.chevron-down class="size-4 transition group-data-open:rotate-180" />
    </button>

    <flux:menu class="hard-shadow w-72 rounded-none! border-2! border-zinc-950! bg-white! p-0!">
        <div class="flex items-center gap-3 border-b-2 border-zinc-950 bg-emerald-600 p-4 text-white">
            <span class="grid size-10 shrink-0 place-items-center bg-orange-600 text-sm font-black">
                {{ auth()->user()->initials() }}
            </span>
            <div class="min-w-0">
                <p class="truncate text-sm font-black">{{ auth()->user()->name }}</p>
                <p class="truncate text-xs font-medium text-emerald-50">{{ auth()->user()->email }}</p>
            </div>
        </div>

        <div class="grid p-2">
            <flux:menu.item
                :href="route('profile.edit')"
                icon="cog"
                class="rounded-none! font-bold data-active:bg-emerald-100!"
                wire:navigate
            >
                Settings
            </flux:menu.item>

            <form method="POST" action="{{ route('logout') }}" class="mt-2 w-full border-t-2 border-zinc-950 pt-2">
                @csrf
                <flux:menu.item
                    as="button"
                    type="submit"
                    icon="arrow-right-start-on-rectangle"
                    class="w-full cursor-pointer rounded-none! font-bold text-orange-700! data-active:bg-orange-100!"
                    data-test="logout-button"
                >
                    Log out
                </flux:menu.item>
            </form>
        </div>
    </flux:menu>
</flux:dropdown>
