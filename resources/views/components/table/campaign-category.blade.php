@php
    // $item = the AdCampaign model, $value = ad_category_id (unused — we want the name, not the id)
@endphp
<span class="text-sm text-[#f5f5f0]/70">{{ $item->adCategory->name ?? '—' }}</span>
