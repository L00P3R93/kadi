@php
    // $value = category.requires_approval, $item = the AdCategory model
@endphp
@if ($value)
    <span class="inline-flex items-center gap-1 rounded-full border border-red-800 bg-red-950/40 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-red-400">
        ⚠ Requires Review
    </span>
@else
    <span class="inline-flex items-center gap-1 rounded-full border border-green-800 bg-green-950/30 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-green-400">
        Auto-Active
    </span>
@endif
