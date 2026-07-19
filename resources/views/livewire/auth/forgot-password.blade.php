<x-layouts::auth :title="__('Forgot password')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Forgot your password?')" :description="__('Enter your email and we’ll send you a link to choose a new one.')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <form method="POST" action="{{ route('password.email') }}" class="flex flex-col gap-5">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                type="email"
                required
                autofocus
                placeholder="email@example.com"
            />

            <flux:button variant="primary" type="submit" class="w-full" data-test="email-password-reset-link-button">
                {{ __('Send reset link') }}
            </flux:button>
        </form>

        <div class="space-x-1 text-center text-sm font-medium text-zinc-700/60 rtl:space-x-reverse">
            <span>{{ __('Remembered it?') }}</span>
            <flux:link :href="route('login')" class="font-bold text-emerald-700 underline decoration-2 underline-offset-4 hover:text-orange-700" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts::auth>
