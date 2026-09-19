<x-layouts::auth :title="__('Finish signing up')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('One last step')"
            :description="__('Welcome, :name. Confirm the details below to create your account.', ['name' => $name])" />

        <form method="POST" action="{{ route('auth.google.complete.store') }}" class="flex flex-col gap-6">
            @csrf

            <x-consent-fields />

            <flux:button type="submit" variant="primary" class="w-full" data-test="google-complete-button">
                {{ __('Create my account') }}
            </flux:button>
        </form>

        <div class="text-center text-sm text-zinc-600 dark:text-zinc-400">
            <a href="{{ route('auth.google.complete.cancel') }}" class="hover:underline">{{ __('Cancel') }}</a>
        </div>
    </div>
</x-layouts::auth>
