<section class="w-full">
    @include('partials.settings-heading')

    <h2 class="sr-only">Tag settings</h2>

    <x-settings.layout current="tags" heading="Tags" subheading="Create labels you can reuse across your collection and wishlist items.">

            <form wire:submit="create" class="grid gap-3 sm:grid-cols-[1fr_auto] sm:items-start">
                <flux:input wire:model="name" label="New tag" placeholder="Coffee gear" maxlength="40" />

                <flux:button type="submit" variant="primary" class="sm:mt-6">Add tag</flux:button>
            </form>

            <div class="mt-8 grid gap-3">
                @forelse ($this->tags as $tag)
                    <div wire:key="tag-{{ $tag->id }}" class="border-2 border-zinc-950 bg-emerald-50 p-4">
                        @if ($editingTagId === $tag->id)
                            <form wire:submit="update" class="grid gap-3 sm:grid-cols-[1fr_auto] sm:items-start">
                                <flux:input wire:model="editingName" label="Tag name" maxlength="40" />

                                <div class="flex gap-2 sm:mt-6">
                                    <flux:button type="button" variant="ghost" wire:click="cancelEditing">Cancel</flux:button>
                                    <flux:button type="submit" variant="primary">Save</flux:button>
                                </div>
                            </form>
                        @else
                            <div class="flex items-center justify-between gap-4">
                                <div class="min-w-0">
                                    <p class="truncate font-black text-zinc-950">{{ $tag->name }}</p>
                                    <p class="mt-1 text-xs font-bold text-zinc-500">
                                        {{ Number::format($tag->collection_items_count + $tag->wishlist_items_count) }} attached items
                                    </p>
                                </div>

                                <div class="flex shrink-0 gap-2">
                                    <flux:button size="sm" variant="outline" wire:click="edit({{ $tag->id }})">Rename</flux:button>
                                    <flux:button size="sm" variant="danger" wire:click="delete({{ $tag->id }})" wire:confirm="Delete this tag? It will be removed from every item.">Delete</flux:button>
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="border-2 border-dashed border-orange-300 bg-orange-50 p-6 text-center">
                        <p class="font-black text-zinc-950">No tags yet</p>
                        <p class="mt-1 text-sm font-medium text-zinc-600">Add one above, then attach it while creating or editing an item.</p>
                    </div>
                @endforelse
            </div>
    </x-settings.layout>
</section>
