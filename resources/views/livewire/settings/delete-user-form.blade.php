<section class="mt-10 border-2 border-dashed border-orange-400 bg-orange-50 p-5 sm:p-6">
    <div class="mb-5">
        <p class="text-[11px] font-black tracking-[0.12em] text-orange-700 uppercase">Danger zone</p>
        <h3 class="mt-1 text-xl font-black tracking-tight">Delete account</h3>
        <p class="mt-2 text-sm font-medium text-zinc-600">Permanently delete your account and everything associated with it.</p>
    </div>

    <flux:modal.trigger name="confirm-user-deletion">
        <flux:button variant="danger" x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')" data-test="delete-account-trigger">
            Delete account
        </flux:button>
    </flux:modal.trigger>

    <flux:modal name="confirm-user-deletion" :show="$errors->isNotEmpty()" focusable class="max-w-lg">
        <form method="POST" wire:submit="deleteUser" class="space-y-6">
            <div>
                <flux:heading size="lg">Are you sure you want to delete your account?</flux:heading>

                <flux:subheading>
                    This cannot be undone. Enter your password to permanently delete your account and all of its data.
                </flux:subheading>
            </div>

            <flux:input wire:model="password" label="Password" type="password" viewable />

            <div class="flex justify-end space-x-2 rtl:space-x-reverse">
                <flux:modal.close>
                    <flux:button variant="filled">Cancel</flux:button>
                </flux:modal.close>

                <flux:button variant="danger" type="submit">Delete account</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
