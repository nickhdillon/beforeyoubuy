@blaze(fold: true)

@aware([ 'placeholder' ])

@props([
    'placeholder' => null,
    'clearable' => null,
    'invalid' => false,
    'suffix' => null,
    'size' => null,
    'max' => 1,
])

@php
// Clearable is not supported on xs size...
if ($size === 'xs') $clearable = null;

$classes = Flux::classes()
    ->add('group/select-button cursor-default py-2')
    ->add('overflow-hidden') // Overflow hidden is here to prevent the button from growing when selected text is too long.
    ->add('flex items-center')
    ->add('shadow-none')
    ->add('bg-white dark:bg-white/10 dark:disabled:bg-white/[7%]')
    // Make the placeholder match the text color of standard input placeholders...
    ->add('disabled:shadow-none')
    ->add(match ($size) {
        default => 'h-10 text-base sm:text-sm rounded-none px-3 block w-full',
        'sm' => 'h-8 text-sm rounded-none ps-3 pe-2 block w-full',
        'xs' => 'h-6 text-xs rounded-none ps-3 pe-2 block w-full',
    })
    ->add($invalid
        ? 'border-2 border-red-500'
        : 'border-2 border-zinc-950 focus-visible:border-emerald-600 focus-visible:outline-none dark:border-zinc-300'
    )
    ;
@endphp

<button type="button" {{ $attributes->class($classes) }} @if ($invalid) data-invalid @endif data-flux-group-target data-flux-select-button>
    <?php if ($slot->isNotEmpty()): ?>
        {{ $slot }}
    <?php else: ?>
        <flux:select.selected :$placeholder :$max :$suffix />
    <?php endif; ?>

    <?php if ($clearable): ?>
        <div
            @class([
                'relative ms-2 -me-2 inline-flex cursor-pointer items-center justify-center rounded-md bg-transparent font-medium text-zinc-500 hover:bg-zinc-800/5 hover:text-zinc-800 dark:text-zinc-400 dark:hover:bg-white/15 dark:hover:text-white',
                'size-6 text-xs' => $size === 'sm',
                'size-8 text-sm' => $size !== 'sm',
                '[[data-flux-select-button]:has([data-flux-select-placeholder])_&]:hidden [[data-flux-select][disabled]:has([data-selected])_&]:hidden',
            ])
            tabindex="-1"
            aria-label="{{ __('Clear selected') }}"
            x-on:click.prevent.stop="$el.closest('ui-select').clear()"
        >
            <flux:icon.x-mark variant="micro" />
        </div>
    <?php endif; ?>

    <flux:icon.chevron-down variant="mini" class="ms-2 -me-1 text-zinc-400/75 [[data-flux-select-button]:hover_&]:text-zinc-800 [[disabled]_&]:text-zinc-200! dark:text-white/60 dark:[[data-flux-select-button]:hover_&]:text-white dark:[[disabled]_&]:text-white/40!" />
</button>
