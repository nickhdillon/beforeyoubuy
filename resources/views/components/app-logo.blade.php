@props([
    'sidebar' => false,
])

<a
    aria-label="Before You Buy dashboard"
    {{ $attributes->class('group flex shrink-0 items-center gap-3') }}
>
    <span class="hard-shadow hard-shadow-group-hover grid size-10 place-items-center border-2 border-zinc-950 bg-emerald-600 group-hover:-translate-y-0.5 sm:size-11">
        <svg class="size-7 sm:size-8" viewBox="0 0 32 32" fill="none" aria-hidden="true">
            <path d="M11 5h15v18H11z" class="fill-orange-600 stroke-zinc-950" stroke-width="2.5" />
            <path d="M5 10h16v17H5z" class="fill-white stroke-zinc-950" stroke-width="2.5" />
            <path d="M9 15h8M9 19h8M9 23h5" class="stroke-emerald-700" stroke-width="2.5" />
        </svg>
    </span>

    <span class="hidden flex-col text-[0.82rem] leading-[0.82] font-black tracking-[-0.045em] uppercase sm:flex sm:text-[0.9rem]">
        <span>Before</span>
        <span class="text-emerald-700">You Buy<span class="ms-1 inline-block size-1.5 bg-orange-600" aria-hidden="true"></span></span>
    </span>
</a>
