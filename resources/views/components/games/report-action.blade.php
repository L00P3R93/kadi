{{-- The "Report" button for a reportable game or round, or its report state once reported. --}}
@props(['reportKey' => null, 'state' => null, 'labels' => []])

@if ($state)
    <span class="rounded-full border border-amber-600/60 bg-amber-900/30 px-2.5 py-1 text-xs text-amber-400">
        {{ $labels[$state] ?? 'Reported' }}
    </span>
@elseif ($reportKey)
    <button
        type="button"
        wire:click="openReport('{{ $reportKey }}')"
        class="rounded-full border border-yellow-800/40 px-3 py-1 text-xs font-semibold text-[#f5f5f0]/80 transition hover:border-[#f5c542]/60 hover:text-[#f5c542]"
    >
        Report
    </button>
@endif
