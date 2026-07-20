<section class="w-full">
    @include('partials.settings-heading')

    <h2 class="sr-only">Security settings</h2>

    <x-settings.layout current="security" heading="Update password" subheading="Use a long, unique password to keep your account secure.">
        <form method="POST" wire:submit="updatePassword" class="space-y-6">
            <flux:input
                wire:model="current_password"
                label="Current password"
                type="password"
                required
                autocomplete="current-password"
                viewable
            />
            <flux:input
                wire:model="password"
                label="New password"
                type="password"
                required
                autocomplete="new-password"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />
            <flux:input
                wire:model="password_confirmation"
                label="Confirm new password"
                type="password"
                required
                autocomplete="new-password"
                passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
                viewable
            />

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit" data-test="update-password-button">Update password</flux:button>
            </div>
        </form>
    </x-settings.layout>
</section>
