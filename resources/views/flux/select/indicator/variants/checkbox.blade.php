@blaze(fold: true, memo: true)

@php
$classes = Flux::classes()
    ->add('shrink-0 size-[1.125rem] rounded-none flex justify-center items-center')
    ->add('text-sm text-zinc-700 dark:text-zinc-800')
    ->add('[ui-option[disabled]_&]:opacity-75 [ui-option[data-selected][disabled]_&]:opacity-50 ')
    ->add('[ui-option[data-selected]_&>svg:first-child]:block')
    ->add([
        'border-2',
        'border-zinc-950 dark:border-zinc-300',
        '[ui-option[disabled]_&]:border-zinc-200 dark:[ui-option[disabled]_&]:border-white/5',
        '[ui-option[data-selected]_&]:border-zinc-950',
        '[ui-option[disabled][data-selected]_&]::border-transparent',
    ])
    ->add([
        'bg-white dark:bg-white/10',
        '[ui-option[data-selected]_&]:bg-emerald-600',
        'hover:[ui-option[data-selected]_&]:bg-emerald-700',
        'focus:[ui-option[data-selected]_&]:bg-emerald-700',
    ])
    ;
@endphp

<div {{ $attributes->class($classes) }}>
    <flux:icon.check variant="micro" class="hidden text-[var(--color-accent-foreground)]" />
    <flux:icon.minus variant="micro" class="hidden text-[var(--color-accent-foreground)]" />
</div>
