@props([
    'model',
    'rating' => null,
    'label' => 'Rating (optional)',
])

@php($currentRating = is_numeric($rating) ? (float) $rating : 0.0)

<fieldset
    class="grid gap-2"
    x-data="starRating({ model: '{{ $model }}', rating: {{ $currentRating }} })"
>
    <legend class="text-sm font-medium text-zinc-950 mb-1">{{ $label }}</legend>

    <div class="flex flex-wrap items-center gap-3">
        <div
            class="flex items-center gap-0.5 rounded-none border-2 border-zinc-950 bg-white px-2 py-1 shadow-none"
            role="radiogroup"
            aria-label="{{ $label }}"
            x-on:mouseleave="stopPreviewing"
        >
            @foreach (range(1, 5) as $star)
                <div class="relative size-6" wire:key="star-{{ $model }}-{{ $star }}">
                    <svg viewBox="0 0 24 24" class="size-6 fill-zinc-200 stroke-zinc-950" stroke-width="1.5" aria-hidden="true">
                        <path d="m12 2.75 2.86 5.8 6.4.93-4.63 4.51 1.09 6.37L12 17.35l-5.72 3.01 1.09-6.37-4.63-4.51 6.4-.93L12 2.75Z" />
                    </svg>

                    <svg
                        x-cloak
                        x-show="displayedRating >= {{ $star }}"
                        viewBox="0 0 24 24"
                        class="absolute inset-0 size-6 fill-orange-500 stroke-zinc-950"
                        stroke-width="1.5"
                        aria-hidden="true"
                    >
                        <path d="m12 2.75 2.86 5.8 6.4.93-4.63 4.51 1.09 6.37L12 17.35l-5.72 3.01 1.09-6.37-4.63-4.51 6.4-.93L12 2.75Z" />
                    </svg>

                    <span
                        x-cloak
                        x-show="displayedRating >= {{ $star - 0.5 }} && displayedRating < {{ $star }}"
                        class="absolute inset-y-0 left-0 w-1/2 overflow-hidden"
                        aria-hidden="true"
                    >
                        <svg viewBox="0 0 24 24" class="size-6 min-w-6 fill-orange-500 stroke-zinc-950" stroke-width="1.5">
                            <path d="m12 2.75 2.86 5.8 6.4.93-4.63 4.51 1.09 6.37L12 17.35l-5.72 3.01 1.09-6.37-4.63-4.51 6.4-.93L12 2.75Z" />
                        </svg>
                    </span>

                    <button
                        type="button"
                        class="absolute inset-y-0 left-0 z-10 w-1/2 cursor-pointer focus:outline-none"
                        aria-label="Rate {{ $star - 0.5 }} stars"
                        x-on:mouseenter="preview({{ $star - 0.5 }})"
                        x-on:focus="preview({{ $star - 0.5 }})"
                        x-on:blur="stopPreviewing"
                        x-on:click="select({{ $star - 0.5 }})"
                    ></button>
                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 z-10 w-1/2 cursor-pointer focus:outline-none"
                        aria-label="Rate {{ $star }} stars"
                        x-on:mouseenter="preview({{ $star }})"
                        x-on:focus="preview({{ $star }})"
                        x-on:blur="stopPreviewing"
                        x-on:click="select({{ $star }})"
                    ></button>
                </div>
            @endforeach
        </div>

        <button type="button" class="text-xs font-black text-zinc-500 underline decoration-2 underline-offset-4 hover:text-zinc-950 focus:outline-none" x-on:click="clear">Clear</button>

        <span x-cloak x-show="displayedRating > 0" class="bg-zinc-950 px-2 py-1 text-xs font-black text-white">
            <span x-text="formatRating(displayedRating)"></span> / 5
        </span>
    </div>

    <flux:error name="{{ $model }}" />
</fieldset>
