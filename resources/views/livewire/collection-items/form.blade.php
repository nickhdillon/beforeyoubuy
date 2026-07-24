<div>
    <flux:modal name="collection-item-form" class="max-w-2xl" wire:close="resetForm">
        <form wire:submit="save" class="grid gap-6">
            <div>
                <p class="text-xs font-black tracking-[0.12em] text-orange-700 uppercase">
                    {{ $item ? 'Item details' : 'Quick capture' }}
                </p>

                <flux:heading size="xl" class="mt-1! font-black! tracking-tight!">
                    {{ $item ? 'Edit '.($item->name ?: 'item') : 'Add to '.$collection->name }}
                </flux:heading>

                @unless ($item)
                    <flux:text class="mt-2! font-medium! text-zinc-600!">Take a photo and save immediately, or add any details you know.</flux:text>
                @endunless
            </div>

            @if ($image || $item)
                <div class="grid gap-5 sm:grid-cols-[180px_1fr] sm:items-start" x-data>
                    <div class="hard-shadow overflow-hidden border-2 border-zinc-950 bg-emerald-50 p-2">
                        @if ($image)
                            <img src="{{ $image->temporaryUrl() }}" alt="Item photo preview" class="aspect-square w-full object-cover" />
                        @elseif ($removeImage)
                            <div class="grid aspect-square place-items-center border-2 border-dashed border-emerald-300 bg-white p-4 text-center text-xs font-black text-zinc-500">
                                Choose a replacement photo
                            </div>
                        @else
                            <img src="{{ Storage::disk('public')->url($item->image_path) }}" alt="Item photo preview" class="aspect-square w-full object-cover" />
                        @endif
                    </div>

                    <flux:field>
                        <flux:label>Photo</flux:label>
                        <input
                            x-ref="replacementImage"
                            type="file"
                            wire:model="image"
                            accept="image/jpeg,image/png,image/webp"
                            capture="environment"
                            class="sr-only"
                            tabindex="-1"
                        >
                        <div class="flex items-center gap-3">
                            <flux:button type="button" variant="outline" icon="photo" x-on:click="$refs.replacementImage.click()">
                                Replace photo
                            </flux:button>
                            <flux:button
                                type="button"
                                variant="danger"
                                icon="trash"
                                tooltip="Remove photo"
                                aria-label="Remove photo"
                                wire:click="removePhoto"
                            />
                        </div>
                        <flux:error name="image" />
                        <flux:description>Photo changes are applied only when you save.</flux:description>
                    </flux:field>
                </div>
            @else
                <flux:field>
                    <flux:label>Photo</flux:label>
                    <flux:input type="file" wire:model="image" accept="image/jpeg,image/png,image/webp" capture="environment" required />
                    <flux:error name="image" />
                </flux:field>
            @endif

            <flux:input wire:model="name" label="Name (optional)" placeholder="French press" />

            <flux:input wire:model="quantity" type="number" min="1" max="9999" label="Quantity" required />

            <x-star-rating model="rating" :$rating />

            <flux:textarea wire:model="notes" label="Notes (optional)" placeholder="Condition, size, where it came from…" rows="3" />

            @unless ($item)
                <div class="border-2 border-dashed border-emerald-300 bg-emerald-50 p-4">
                    <flux:checkbox
                        wire:model="createAnother"
                        label="Create another after this"
                        description="Save this item, clear the form, and stay here for the next photo."
                    />
                </div>
            @endunless

            <div class="flex gap-3 items-center">
                @if ($item)
                    <flux:modal.trigger name="delete-collection-item">
                        <flux:button type="button" variant="danger">
                            Delete
                        </flux:button>
                    </flux:modal.trigger>
                @endif

                <div class="flex gap-3 ml-auto">
                    <flux:modal.close>
                        <flux:button variant="ghost">Cancel</flux:button>
                    </flux:modal.close>

                    <flux:button variant="primary" type="submit" wire:target="save" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="save">{{ $item ? 'Save' : 'Add' }}</span>
                        <span wire:loading wire:target="save">{{ $item ? 'Saving…' : 'Saving photo…' }}</span>
                    </flux:button>
                </div>
            </div>
        </form>
    </flux:modal>

    @if ($item)
        <flux:modal name="delete-collection-item" class="max-w-md">
            <div class="grid gap-6">
                <div>
                    <flux:heading size="lg" class="font-black!">Delete item?</flux:heading>
                    <flux:text class="mt-2! font-medium! text-zinc-600!">
                        This will permanently delete {{ $item->name ?: 'this item' }} and its photo. This cannot be undone.
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
