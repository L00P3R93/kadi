<x-layouts::auth :title="__('Confirm to continue')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Before you continue')"
            :description="__('Please confirm your age and accept our terms to keep playing on Kadi.')" />

        <form method="POST" action="{{ route('consent.store') }}" class="flex flex-col gap-6">
            @csrf

            <x-consent-fields />

            <flux:button type="submit" variant="primary" class="w-full" data-test="consent-button">
                {{ __('Continue') }}
            </flux:button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="text-center text-sm text-zinc-600 dark:text-zinc-400">
            @csrf
            <button type="submit" class="hover:underline">{{ __('Log out') }}</button>
        </form>
    </div>
</x-layouts::auth>
