@blaze(fold: true)

@aware([ 'placeholder' ])

@props([
    'placeholder' => null,
    'clearable' => null,
    'size' => null,
])

@php
$loading = ($wireModel = $attributes->wire('model')) && $wireModel->directive && $wireModel->hasModifier('live');

if ($loading) {
    $attributes = $attributes->merge(['wire:loading.attr' => 'data-flux-loading']);
}
@endphp

<flux:input :$size :$placeholder :$attributes class:input="data-invalid:outline-red-500">
    <x-slot name="iconTrailing">
        <?php if ($clearable): ?>
            <div
                @class([
                    'relative ms-2 -me-3 inline-flex cursor-pointer items-center justify-center rounded-md bg-transparent font-medium text-zinc-500 hover:bg-zinc-800/5 hover:text-zinc-800 dark:text-zinc-400 dark:hover:bg-white/15 dark:hover:text-white',
                    'size-6 text-xs' => $size === 'sm',
                    'size-8 text-sm' => $size !== 'sm',
                    '[[data-flux-input]:has(input:placeholder-shown)_&]:hidden [[data-flux-select]:has([disabled][data-selected])_&]:hidden',
                ])
                tabindex="-1"
                aria-label="{{ __('Clear selected') }}"
                x-on:click.prevent.stop="$el.closest('ui-select').clear()"
            >
                <flux:icon.x-mark variant="micro" />
            </div>
        <?php endif; ?>

        <flux:button size="sm" square variant="subtle" tabindex="-1" class="-me-1 [[disabled]_&]:pointer-events-none">
            <flux:icon.chevron-up-down variant="mini" class="text-zinc-400/75 [[data-flux-input]:hover_&]:text-zinc-800 [[disabled]_&]:text-zinc-200! dark:text-white/60 dark:[[data-flux-input]:hover_&]:text-white dark:[[disabled]_&]:text-white/40!" />
        </flux:button>
    </x-slot>
</flux:input>
