<flux:modal name="collection-form" class="max-w-lg" wire:close="resetForm">
    <form wire:submit="save" class="grid gap-6">
        <div>
            <p class="text-xs font-black tracking-[0.12em] text-orange-700 uppercase">
                {{ $collection ? 'Collection settings' : 'New collection' }}
            </p>
            <flux:heading size="xl" class="mt-1! font-black! tracking-tight!">
                {{ $collection ? 'Edit collection' : 'Give your things a home' }}
            </flux:heading>
            <flux:text class="mt-2! font-medium! text-zinc-600!">
                {{ $collection ? 'Update how this collection is named, described, and shared.' : 'You only need a name. Everything else can evolve later.' }}
            </flux:text>
        </div>

        <flux:input wire:model="name" label="Name" placeholder="Coffee gear" required autofocus />
        <flux:textarea wire:model="description" label="Description (optional)" placeholder="What belongs in this collection?" rows="4" />

        <div class="border-2 border-dashed border-emerald-300 bg-emerald-50 p-4">
            <flux:switch wire:model="isPublic" label="Share publicly" description="Anyone with the link can see the collection and its items. Your wishlist always stays private." />
        </div>

        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
            <flux:modal.close>
                <flux:button variant="ghost" class="w-full sm:w-auto">Cancel</flux:button>
            </flux:modal.close>
            <flux:button variant="primary" type="submit" class="w-full sm:w-auto" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="save">{{ $collection ? 'Save changes' : 'Create collection' }}</span>
                <span wire:loading wire:target="save">{{ $collection ? 'Saving…' : 'Creating…' }}</span>
            </flux:button>
        </div>
    </form>
</flux:modal>
