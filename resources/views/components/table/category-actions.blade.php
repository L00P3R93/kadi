@php
    // $item = the AdCategory model ($value is unused here — there's no 'actions' db column)
@endphp
<div class="text-right">
    <button
        type="button"
        wire:click="$dispatch('category-edit', { id: {{ $item->id }} })"
        class="rounded-lg border border-[#f5c542]/20 px-3 py-1.5 text-[11px] font-semibold text-[#f5f5f0]/70 transition hover:border-[#f5c542]/50 hover:text-[#f5c542]"
    >
        Edit
    </button>
</div>
