@props(['result' => null])

@if ($result === 'win')
    <span class="rounded-full border border-green-700 bg-green-900/50 px-2 py-0.5 text-xs text-green-400">Won</span>
@elseif ($result === 'loss')
    <span class="rounded-full border border-red-700 bg-red-900/50 px-2 py-0.5 text-xs text-red-400">Lost</span>
@endif
