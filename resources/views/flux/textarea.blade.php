@blaze(fold: true, unsafe: [
    'name', 'label', 'badge',
    'description', 'description:trailing',
    'label:badge', 'label:aside', 'label:trailing',
    'error:name', 'error:bag', 'error:message', 'error:icon', 'error:nested', 'error:deep',
])

@props([
    'name' => $attributes->whereStartsWith('wire:model')->first(),
    'resize' => 'vertical',
    'invalid' => null,
    'rows' => 4,
])

@php
$classes = Flux::classes()
    ->add('block w-full rounded-none border-2 p-3 shadow-none disabled:shadow-none dark:shadow-none')
    ->add('bg-white dark:bg-white/10 dark:disabled:bg-white/[7%]')
    ->add(match ($resize) {
        'none' => 'resize-none',
        'both' => 'resize',
        'horizontal' => 'resize-x',
        'vertical' => 'resize-y',
    })
    ->add($rows === 'auto' ? 'field-sizing-content' : '')
    ->add('text-base text-zinc-700 placeholder-zinc-400 disabled:text-zinc-500 disabled:placeholder-zinc-400/70 sm:text-sm dark:text-zinc-300 dark:placeholder-zinc-400 dark:disabled:text-zinc-400 dark:disabled:placeholder-zinc-500')
    ->add('border-zinc-950 focus:border-emerald-600 focus:ring-0 disabled:border-zinc-950/50 dark:border-zinc-300 dark:disabled:border-zinc-300/50')
    ->add('data-invalid:border-red-500 data-invalid:shadow-none dark:data-invalid:border-red-500');
@endphp

<flux:with-field :$attributes>
    <textarea
        {{ $attributes->class($classes) }}
        rows="{{ $rows }}"
        @isset($name) name="{{ $name }}" @endisset
        @unblaze(scope: ['name' => $name ?? null, 'invalid' => $invalid ?? false])
        <?php if ($scope['invalid'] || ($scope['name'] && $errors->has($scope['name']))): ?>
        aria-invalid="true" data-invalid
        <?php endif; ?>
        @endunblaze
        data-flux-control
        data-flux-textarea
    >{{ $slot }}</textarea>
</flux:with-field>
