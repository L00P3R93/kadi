@php
    // $value = category.is_active, $item = the AdCategory model
@endphp
<label class="relative inline-flex cursor-pointer items-center">
    <input
        type="checkbox"
        wire:click="$dispatch('category-toggle-active', { id: {{ $item->id }} })"
        @checked($value)
        class="peer sr-only"
    >
    <div class="h-5 w-9 rounded-full bg-gray-700 transition-colors peer-checked:bg-[#f5c542]"></div>
    <div class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white transition-transform peer-checked:translate-x-4"></div>
</label>
