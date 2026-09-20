<x-layouts::auth :title="__('Choose a new name')">
    <div class="flex flex-col gap-6">
        <x-auth-header
            :title="__('Choose a new player name')"
            :description="__('Your current name no longer meets our naming rules. Please pick a new one to keep playing on Kadi.')" />

        <form method="POST" action="{{ route('name.update') }}" class="flex flex-col gap-6">
            @csrf

            <x-player-name-field :value="old('name', $suggestion)" autofocus />

            @if ($suggestion !== '' && ! old('name'))
                <p class="-mt-4 text-xs text-zinc-500">{{ __('We suggested a name based on your current one. Change it if you like.') }}</p>
            @endif

            <flux:button type="submit" variant="primary" class="w-full" data-test="update-name-button">
                {{ __('Save name') }}
            </flux:button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="text-center text-sm text-zinc-600 dark:text-zinc-400">
            @csrf
            <button type="submit" class="hover:underline">{{ __('Log out') }}</button>
        </form>
    </div>
</x-layouts::auth>
