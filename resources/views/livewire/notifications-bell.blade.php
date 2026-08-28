<div x-data="{ open: false }" @keydown.escape.window="open = false">
    {{-- Bell trigger --}}
    <button
        type="button"
        wire:click="show"
        x-on:click="open = true"
        class="relative flex h-10 w-10 items-center justify-center rounded-full border border-yellow-800/30 text-[#f5f5f0]/70 transition hover:border-[#f5c542]/50 hover:text-[#f5c542]"
        aria-label="{{ __('Notifications') }}"
        :aria-expanded="open"
    >
        <flux:icon.bell variant="outline" class="size-5" />
        @if ($unreadCount > 0)
            <span
                class="absolute -top-1 -right-1 flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-[#f5c542] px-1 text-[10px] font-bold text-black"
            >{{ $unreadLabel }}</span>
        @endif
    </button>

    {{-- Backdrop + slide-over panel --}}
    <div x-cloak x-show="open" class="fixed inset-0 z-[70]">
        <div
            x-show="open"
            x-transition:enter="transition-opacity ease-linear duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-on:click="open = false; $wire.hide()"
            class="absolute inset-0 bg-black/60 backdrop-blur-sm"
        ></div>

        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="translate-x-0"
            x-transition:leave-end="translate-x-full"
            role="dialog"
            aria-modal="true"
            aria-label="{{ __('Notifications') }}"
            class="absolute inset-y-0 right-0 flex w-full max-w-sm flex-col border-l border-yellow-800/40 bg-[#111111] shadow-2xl"
        >
            {{-- Panel header --}}
            <div class="flex h-16 flex-shrink-0 items-center justify-between border-b border-yellow-800/20 px-5">
                <h2 class="text-lg font-bold text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">
                    {{ __('Notifications') }}
                </h2>
                <button
                    type="button"
                    x-on:click="open = false; $wire.hide()"
                    class="flex h-8 w-8 items-center justify-center rounded-lg text-[#6b6b6b] transition hover:bg-[#1a1a1a] hover:text-[#f5c542]"
                    aria-label="{{ __('Close notifications') }}"
                >
                    <flux:icon.x-mark variant="outline" class="size-5" />
                </button>
            </div>

            {{-- Notification list --}}
            <div class="flex-1 divide-y divide-yellow-800/10 overflow-y-auto">
                @if ($notifications->isEmpty())
                    <div class="flex h-full flex-col items-center justify-center gap-3 px-6 py-12 text-center">
                        <flux:icon.check-badge variant="outline" class="size-10 text-[#6b6b6b]" />
                        <p class="text-sm text-[#6b6b6b]">{{ __('You are all caught up.') }}</p>
                    </div>
                @else
                    @foreach ($notifications as $notification)
                        <button
                            type="button"
                            wire:key="notification-{{ $notification->id }}"
                            wire:click="markAsRead('{{ $notification->id }}')"
                            x-on:click="$wire.markAsRead('{{ $notification->id }}')"
                            @class([
                                'relative w-full px-5 py-4 text-left transition hover:bg-[#161616]',
                                'bg-[#161616]/60' => $notification->read_at === null,
                            ])
                        >
                            <span class="flex items-start gap-3">
                                <span
                                    class="mt-1.5 size-2 flex-shrink-0 rounded-full {{
                                        $notification->read_at === null ? 'bg-[#f5c542]' : 'bg-transparent'
                                    }}"
                                    aria-hidden="true"
                                ></span>
                                <span class="min-w-0">
                                    <span class="block truncate text-sm font-medium {{ $notification->read_at === null ? 'text-[#f5f5f0]' : 'text-[#f5f5f0]/60' }}">
                                        {{ $notification->data['change'] ?? __('Notification') }}
                                    </span>
                                    <span class="mt-1 block text-xs text-[#6b6b6b]">
                                        {{ $notification->created_at->diffForHumans() }}
                                    </span>
                                </span>
                            </span>
                        </button>
                    @endforeach
                @endif
            </div>

            {{-- Panel footer --}}
            <div class="flex-shrink-0 border-t border-yellow-800/20 px-5 py-3">
                <button
                    type="button"
                    wire:click="markAllAsRead"
                    wire:loading.attr="disabled"
                    wire:target="markAllAsRead"
                    class="text-sm font-semibold text-[#f5c542] transition hover:text-[#ffde74] disabled:opacity-50"
                >
                    {{ __('Mark all as read') }}
                </button>
            </div>
        </div>
    </div>
</div>
