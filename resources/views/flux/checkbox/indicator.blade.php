@blaze(fold: true, memo: true)

@php
$classes = Flux::classes()
    ->add('flex size-5 shrink-0 items-center justify-center rounded-none')
    ->add('text-sm text-zinc-950 dark:text-white')
    ->add('shadow-none [ui-checkbox[disabled]_&]:opacity-50')
    ->add('[ui-checkbox[data-checked]:not([data-indeterminate])_&>svg:first-child]:block [ui-checkbox[data-indeterminate]_&>svg:last-child]:block')
    ->add([
        'border-2',
        'border-zinc-950 dark:border-zinc-300',
        '[ui-checkbox:focus-visible_&]:border-emerald-600',
        '[ui-checkbox[data-checked]_&]:border-zinc-950 [ui-checkbox[data-indeterminate]_&]:border-zinc-950',
        'dark:[ui-checkbox[data-checked]_&]:border-zinc-300 dark:[ui-checkbox[data-indeterminate]_&]:border-zinc-300',
        '[print-color-adjust:exact]',
    ])
    ->add([
        'bg-white dark:bg-zinc-800',
        '[ui-checkbox[data-checked]_&]:bg-emerald-600',
        '[ui-checkbox[data-indeterminate]_&]:bg-emerald-600',
    ])
    ;
@endphp

<div {{ $attributes->class($classes) }} data-flux-checkbox-indicator>
    <flux:icon.check variant="micro" class="hidden text-white" />
    <flux:icon.minus variant="micro" class="hidden text-white" />
</div>
