@props([
    'title',
    'description',
])

<div class="flex w-full flex-col gap-2 text-left">
    <flux:heading size="xl" class="!text-3xl !font-black !tracking-[-0.04em] !text-zinc-950 sm:!text-4xl">{{ $title }}</flux:heading>
    <flux:subheading class="!text-sm !leading-relaxed !font-medium !text-zinc-700/60 sm:!text-base">{{ $description }}</flux:subheading>
</div>
