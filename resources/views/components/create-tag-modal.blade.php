@props(['name'])

<flux:modal :$name class="max-w-md">
    <form wire:submit="createTag" class="grid gap-6">
        <div>
            <flux:heading size="lg" class="font-black!">Create tag</flux:heading>
            <flux:text class="mt-2! font-medium! text-zinc-600!">
                The new tag will be selected for this item automatically.
            </flux:text>
        </div>

        <flux:input wire:model="tagForm.name" label="Tag name" placeholder="Coffee gear" maxlength="40" autofocus />

        <div class="flex justify-end gap-3">
            <flux:modal.close>
                <flux:button type="button" variant="ghost">Cancel</flux:button>
            </flux:modal.close>

            <flux:button type="submit" variant="primary" wire:loading.attr="disabled" wire:target="createTag">
                <span wire:loading.remove wire:target="createTag">Add tag</span>
                <span wire:loading wire:target="createTag">Adding…</span>
            </flux:button>
        </div>
    </form>
</flux:modal>
