@php
    // $value = campaign.status, $item = the AdCategory model
    use App\Enums\CampaignStatus;
    $status = $value instanceof CampaignStatus ? $value : CampaignStatus::from($value);
@endphp
<span class="inline-flex items-center gap-1 rounded-full border {{ $status->color() }} px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide">
    @svg($status->icon(), 'h-3 w-3')
    {{ $status->label() }}
</span>
