@blaze(fold: true)

@aware([ 'indicator' ])

@props([
    'filterable' => null,
    'indicator' => null,
    'loading' => null,
    'label' => null,
    'value' => null,
])

@php
$classes = Flux::classes()
    ->add('group/option overflow-hidden data-hidden:hidden group flex items-center px-2 py-1.5 w-full focus:outline-hidden')
    ->add('rounded-none')
    ->add('text-start text-sm font-black select-none')
    ->add('text-zinc-950 data-active:bg-emerald-100 data-selected:bg-emerald-50 [&[disabled]]:text-zinc-400 dark:text-white dark:data-active:bg-emerald-950 dark:data-selected:bg-emerald-950/60 dark:[&[disabled]]:text-zinc-400')
    ;

$livewireAction = $attributes->whereStartsWith('wire:click')->isNotEmpty();
$alpineAction = $attributes->whereStartsWith('x-on:click')->isNotEmpty();

$loading ??= $loading ?? $livewireAction;

if ($loading) {
    $attributes = $attributes->merge(['wire:loading.attr' => 'data-flux-loading']);
}
@endphp

<ui-option
    @if ($value !== null) value="{{ $value }}" @endif
    @if ($value) wire:key="{{ $value }}" @endif
    @if ($filterable === false) filter="manual" @endif
    @if ($livewireAction || $alpineAction) action @endif
    {{ $attributes->class($classes) }}
    data-flux-option
>
    <div class="w-6 shrink-0 [ui-selected_&]:hidden">
        <flux:select.indicator :variant="$indicator" />
    </div>

    {{ $slot->isNotEmpty() ? $slot : $label }}

    <?php if ($loading): ?>
        <flux:icon.loading class="hidden [[data-flux-loading]>&]:block ms-auto text-zinc-400 [[data-flux-menu-item]:hover_&]:text-current" variant="micro" />
    <?php endif; ?>
</ui-option>
