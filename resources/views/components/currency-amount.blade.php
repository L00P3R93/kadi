@props(['amount', 'decimals' => 0])
@php
    $currency = session('currency', ['code' => 'KES', 'symbol' => 'KES']);
@endphp
<span {{ $attributes }}>
    {{ $currency['code'] }} {{ number_format($amount, $decimals) }} {{-- 'Coins' --}}
</span>
