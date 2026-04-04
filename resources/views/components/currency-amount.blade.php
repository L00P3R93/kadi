@props(['amount', 'decimals' => 2])
@php
    $currency = session('currency', ['code' => 'KES', 'symbol' => 'KES']);
@endphp
<span {{ $attributes }}>
    {{ $currency['code'] }} {{ number_format($amount, $decimals) }}
</span>
