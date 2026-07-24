@blaze(fold: true, unsafe: [
    // flux:with-inline-field props
    'name', 'label', 'description',
])

@props([
    'name' => null,
    'align' => 'right',
    'checked' => null
])

@php
// We only want to show the name attribute it has been set manually
// but not if it has been set from the `wire:model` attribute...
$showName = isset($name);
if (! isset($name)) {
    $name = $attributes->whereStartsWith('wire:model')->first();
}

$classes = Flux::classes()
    ->add('group relative inline-flex h-6 w-10 min-w-10 items-center rounded-none border-2 border-zinc-950 bg-white outline-none transition-colors')
    ->add('focus-visible:border-emerald-600 focus-visible:outline-none')
    ->add('[&[disabled]]:opacity-50 dark:border-zinc-300 dark:bg-zinc-800')
    ->add('[print-color-adjust:exact]')
    ->add([
        'data-checked:border-zinc-950 data-checked:bg-emerald-600',
        'dark:data-checked:border-zinc-300',
    ])
    ;

$indicatorClasses = Flux::classes()
    ->add('size-4 rounded-none bg-zinc-950 transition-transform')
    ->add('translate-x-0.5 rtl:-translate-x-0.5')
    ->add([
        'group-data-checked:translate-x-4.5 rtl:group-data-checked:-translate-x-4.5',
        'group-data-checked:bg-white',
    ]);
@endphp

<?php if ($align === 'left' || $align === 'start'): ?>
    <flux:with-inline-field :$attributes>
        <ui-switch {{ $attributes->class($classes) }} @if($showName) name="{{ $name }}" @endif @if($checked) checked data-checked @endif data-flux-control data-flux-switch>
            <span class="{{ \Illuminate\Support\Arr::toCssClasses($indicatorClasses) }}"></span>
        </ui-switch>
    </flux:with-inline-field>
<?php else: ?>
    <flux:with-reversed-inline-field :$attributes>
        <ui-switch {{ $attributes->class($classes) }} @if($showName) name="{{ $name }}" @endif @if($checked) checked data-checked @endif data-flux-control data-flux-switch>
            <span class="{{ \Illuminate\Support\Arr::toCssClasses($indicatorClasses) }}"></span>
        </ui-switch>
    </flux:with-reversed-inline-field>
<?php endif; ?>
