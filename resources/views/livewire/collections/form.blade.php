<div>
    <flux:modal name="collection-form" class="max-w-lg" wire:close="resetForm">
        <form wire:submit="save" class="grid gap-6">
            <div>
                <p class="text-xs font-black tracking-[0.12em] text-orange-700 uppercase">
                    {{ $collection ? 'Collection settings' : 'New collection' }}
                </p>

                <flux:heading size="xl" class="mt-1! font-black! tracking-tight!">
                    {{ $collection ? 'Edit collection' : 'Give your things a home' }}
                </flux:heading>
            </div>

            <flux:input wire:model="name" label="Name" placeholder="Coffee gear" required autofocus />

            <flux:textarea wire:model="description" label="Description (optional)" placeholder="What belongs in this collection?" rows="4" />

            <div class="border-2 border-dashed border-emerald-300 bg-emerald-50 p-4">
                <flux:switch wire:model="isPublic" label="Share publicly" description="Anyone with the link can see the collection and its items. Your wishlist always stays private." />
            </div>

            <div class="flex gap-3 items-center">
                @if ($collection)
                    <flux:modal.trigger name="delete-collection">
                        <flux:button type="button" variant="danger">
                            Delete
                        </flux:button>
                    </flux:modal.trigger>
                @endif

                <div class="flex gap-3 ml-auto">
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancel</flux:button>
                    </flux:modal.close>

                    <flux:button variant="primary" type="submit" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">{{ $collection ? 'Save' : 'Create' }}</span>
                        <span wire:loading wire:target="save">{{ $collection ? 'Saving…' : 'Creating…' }}</span>
                    </flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    @if ($collection)
        <flux:modal name="delete-collection" class="max-w-md">
            <div class="grid gap-6">
                <div>
                    <flux:heading size="lg" class="font-black!">Delete collection?</flux:heading>
                    <flux:text class="mt-2! font-medium! text-zinc-600!">
                        This will permanently delete {{ $collection->name }}, all of its items, and their photos. This cannot be undone.
                    </flux:text>
                </div>

                <div class="flex gap-3 justify-end">
                    <flux:modal.close>
                        <flux:button type="button" variant="ghost">Cancel</flux:button>
                    </flux:modal.close>

                    <flux:button type="button" variant="danger" wire:click="delete" wire:loading.attr="disabled" wire:target="delete">
                        <span wire:loading.remove wire:target="delete">Delete</span>
                        <span wire:loading wire:target="delete">Deleting…</span>
                    </flux:button>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
