<x-layouts::app.header :title="$title ?? null">
    <main class="mx-auto flex w-full max-w-7xl flex-1 px-4 py-8 sm:px-8 sm:py-10 lg:px-10 lg:py-12">
        {{ $slot }}
    </main>
</x-layouts::app.header>
