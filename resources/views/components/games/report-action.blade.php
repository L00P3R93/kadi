{{-- The "Report" button for a reportable game or round while its report window is open, or its report state once reported. --}}
@props(['reportKey' => null, 'state' => null, 'labels' => [], 'expiresAt' => null])

@php
    $windowOpen = $reportKey && \App\Support\PlayedGame::reportWindowOpen($expiresAt);
    $deadline = $expiresAt ? \Illuminate\Support\Carbon::parse($expiresAt) : null;
@endphp

@if ($state)
    <span class="rounded-full border border-amber-600/60 bg-amber-900/30 px-2.5 py-1 text-xs text-amber-400">
        {{ $labels[$state] ?? 'Reported' }}
    </span>
@elseif ($windowOpen)
    <span class="flex items-center gap-2">
        <span class="text-xs text-[#6b6b6b]" title="You can report until {{ $deadline->format('j M Y, H:i') }}">
            {{ $deadline->diffForHumans(['syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE, 'parts' => 1]) }} left
        </span>
        <button
            type="button"
            wire:click="openReport('{{ $reportKey }}')"
            class="rounded-full border border-yellow-800/40 px-3 py-1 text-xs font-semibold text-[#f5f5f0]/80 transition hover:border-[#f5c542]/60 hover:text-[#f5c542]"
        >
            Report
        </button>
    </span>
@elseif ($reportKey)
    <span class="text-xs text-[#6b6b6b]" @if ($deadline) title="Reporting closed {{ $deadline->format('j M Y, H:i') }}" @endif>
        Reporting closed
    </span>
@endif
