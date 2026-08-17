@blaze

@props([
    'clearable' => true,
    'closable' => null,
    'icon' => null,
])

@php
// Clerable or closable, not both...
if ($closable !== null) $clearable = null;

$classes = Flux::classes()
    ->add('h-10 w-full flex items-center px-3 py-2')
    ->add('font-bold text-base sm:text-sm text-zinc-800 dark:text-white')
    ->add('ps-9') // Make room for magnifying glass icon...
    ->add('pe-9') // Make room for clear/clos button and loading indicator...
    ->add('outline-hidden')
    ->add('border-b-2 border-zinc-950 dark:border-zinc-300')
    ->add('bg-white dark:bg-zinc-800')
    // The below reverts styles added by Tailwind Forms plugin
    ->add('border-t-0 border-s-0 border-e-0 focus:ring-0 focus:border-emerald-600 dark:focus:border-emerald-500')
    ->add('data-invalid:text-red-500 dark:data-invalid:text-red-400')
    ;

$name = $attributes->whereStartsWith('wire:model')->first();

$invalid ??= ($name && $errors->has($name));

$loading = ($wireModel = $attributes->wire('model')) && $wireModel->directive && $wireModel->hasModifier('live');

if ($loading) {
    $attributes = $attributes->merge(['wire:loading.attr' => 'data-flux-loading']);
}
@endphp

<div class="relative flex grow mx-[-5px] mt-[-5px] mb-[5px]" data-flux-select-search>
    <div class="absolute top-0 bottom-0 flex items-center justify-center text-xs text-zinc-400 ps-3.5 start-0">
        <?php if (is_string($icon)): ?>
            <flux:icon :$icon variant="micro" />
        <?php elseif ($icon): ?>
            {{ $icon }}
        <?php else: ?>
            <flux:icon.magnifying-glass variant="micro" />
        <?php endif; ?>
    </div>

    <input
        type="text"
        @if ($invalid) aria-invalid="true" data-invalid @endif
        {{ $attributes->class($classes)->merge(['placeholder' => __('Search...')]) }}
    />

    <?php if ($loading): ?>
        <div class="opacity-0 [[data-flux-select-search]:has([data-flux-loading])_&]:opacity-100 transition-opacity absolute top-0 bottom-0 flex items-center justify-center pe-2.5 end-0">
            <flux:icon.loading class="text-zinc-400 [[data-flux-menu-item]:hover_&]:text-current" variant="mini" />
        </div>
    <?php endif; ?>

    <?php if ($closable): ?>
        <div class="[[data-flux-select-search]:has([data-flux-loading])_&]:opacity-0 transition-opacity absolute top-0 bottom-0 flex items-center justify-center pe-1 end-0">
            <ui-close>
                <button type="button" class="relative inline-flex size-8 items-center justify-center rounded-md bg-transparent text-sm font-medium text-zinc-500 hover:bg-zinc-800/5 hover:text-zinc-800 dark:text-zinc-400 dark:hover:bg-white/15 dark:hover:text-white" aria-label="{{ __('Clear search input') }}">
                    <flux:icon.x-mark variant="micro" />
                </button>
            </ui-close>
        </div>
    <?php elseif ($clearable): ?>
        <div class="[[data-flux-select-search]:has([data-flux-loading])_&]:opacity-0 transition-opacity absolute top-0 bottom-0 flex items-center justify-center pe-1 end-0 [[data-flux-select-search]:has(input:placeholder-shown)_&]:hidden">
            <button type="button" class="relative inline-flex size-8 items-center justify-center rounded-md bg-transparent text-sm font-medium text-zinc-500 hover:bg-zinc-800/5 hover:text-zinc-800 dark:text-zinc-400 dark:hover:bg-white/15 dark:hover:text-white" tabindex="-1" aria-label="{{ __('Clear command input') }}"
                x-data="fluxSelectSearchClearable"
                x-on:click="clear()"
            >
                <flux:icon.x-mark variant="micro" />
            </button>
        </div>
    <?php endif; ?>
</div>
