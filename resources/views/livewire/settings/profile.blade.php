<section class="w-full">
    @include('partials.settings-heading')

    <h2 class="sr-only">Profile settings</h2>

    <x-settings.layout current="profile" heading="Profile" subheading="Update the details tied to your account.">
        <form wire:submit="updateProfileInformation" class="w-full space-y-6">
            <flux:input wire:model="name" label="Name" type="text" required autofocus autocomplete="name" />

            <flux:input wire:model="email" label="Email address" type="email" required autocomplete="email" />

            <div class="flex items-center gap-4">
                <flux:button variant="primary" type="submit">Save changes</flux:button>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
