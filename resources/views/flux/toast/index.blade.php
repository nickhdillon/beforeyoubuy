@blaze(fold: true, safe: ['position'])

@props([
    'position' => 'bottom end',
])

<ui-toast x-data x-on:toast-show.document="! $el.closest('ui-toast-group') && $el.showToast($event.detail)" popover="manual" position="{{ $position }}" wire:ignore>
    <template>
        <div {{ $attributes->only(['class'])->class('max-w-sm in-[ui-toast-group]:max-w-auto in-[ui-toast-group]:w-xs sm:in-[ui-toast-group]:w-sm') }} data-variant="" data-flux-toast-dialog>
            <div class="hard-shadow relative flex overflow-hidden rounded-none border-2 border-zinc-950 bg-white p-1.5 before:absolute before:inset-y-0 before:left-0 before:w-1.5 before:bg-zinc-950 [[data-flux-toast-dialog][data-variant=success]_&]:before:bg-emerald-600 [[data-flux-toast-dialog][data-variant=warning]_&]:before:bg-orange-500 [[data-flux-toast-dialog][data-variant=danger]_&]:before:bg-red-600">
                <div class="flex flex-1 items-center gap-2 overflow-hidden ps-3">
                    <div class="grid size-6 shrink-0 place-items-center border-2 border-zinc-950 bg-zinc-100 text-zinc-950 [[data-flux-toast-dialog][data-variant=success]_&]:bg-emerald-600 [[data-flux-toast-dialog][data-variant=success]_&]:text-white [[data-flux-toast-dialog][data-variant=warning]_&]:bg-orange-500 [[data-flux-toast-dialog][data-variant=warning]_&]:text-white [[data-flux-toast-dialog][data-variant=danger]_&]:bg-red-600 [[data-flux-toast-dialog][data-variant=danger]_&]:text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="hidden size-4 [[data-flux-toast-dialog][data-variant=success]_&]:block">
                            <path fill-rule="evenodd" d="M13.28 4.22a.75.75 0 0 1 0 1.06l-6.5 6.5a.75.75 0 0 1-1.06 0l-3-3a.75.75 0 0 1 1.06-1.06l2.47 2.47 5.97-5.97a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" />
                        </svg>

                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="hidden size-4 [[data-flux-toast-dialog][data-variant=warning]_&]:block [[data-flux-toast-dialog][data-variant=danger]_&]:block">
                            <path fill-rule="evenodd" d="M8 1.75a.75.75 0 0 1 .75.75v6a.75.75 0 0 1-1.5 0v-6A.75.75 0 0 1 8 1.75ZM8 14a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                        </svg>

                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="size-4 [[data-flux-toast-dialog][data-variant=success]_&]:hidden [[data-flux-toast-dialog][data-variant=warning]_&]:hidden [[data-flux-toast-dialog][data-variant=danger]_&]:hidden">
                            <path fill-rule="evenodd" d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14ZM9 5a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM6.75 8a.75.75 0 0 0 0 1.5h.5v2a.75.75 0 0 0 1.5 0V8.75A.75.75 0 0 0 8 8h-1.25Z" clip-rule="evenodd" />
                        </svg>
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="text-sm leading-tight font-black text-zinc-950 [&:not(:empty)]:pb-0.5"><slot name="heading"></slot></div>
                        <div class="text-sm leading-tight font-bold text-zinc-700"><slot name="text"></slot></div>

                        <template name="link">
                            <a class="mt-2 block text-sm font-black text-emerald-700 underline decoration-2 underline-offset-4 hover:text-emerald-600"><slot name="text"></slot></a>
                        </template>
                    </div>

                    <ui-close>
                        <button type="button" class="grid size-7 place-items-center border-2 border-transparent bg-transparent text-zinc-500 transition-colors hover:border-zinc-950 hover:bg-orange-100 hover:text-zinc-950" aria-label="Close notification">
                            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                            </svg>
                        </button>
                    </ui-close>
                </div>
            </div>
        </div>
    </template>
</ui-toast>
