@blaze(fold: true)

@props([
    'name' => null,
])

@php
// We only want to show the name attribute on the checkbox if it has been set
// manually, but not if it has been set from the wire:model attribute...
$showName = isset($name);

if (! isset($name)) {
    $name = $attributes->whereStartsWith('wire:model')->first();
}

$classes = Flux::classes()
    ->add('mt-px flex size-5 rounded-none outline-none focus-visible:outline-none')
    ;
@endphp

<flux:with-inline-field :$attributes>
    <ui-checkbox {{ $attributes->class($classes) }} @if($showName) name="{{ $name }}" @endif data-flux-control data-flux-checkbox>
        <flux:checkbox.indicator />
    </ui-checkbox>
</flux:with-inline-field>
