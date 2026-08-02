@php
    // $value = category.pricing_multiplier, $item = the AdCategory model
@endphp
<span class="inline-flex items-center rounded-full border border-[#f5c542]/25 bg-[#f5c542]/10 px-2.5 py-0.5 font-cinzel text-xs font-bold text-[#f5c542]">
    {{ number_format((float) $value, 2) }}x
</span>
