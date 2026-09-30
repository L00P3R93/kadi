@props(['amount', 'decimals' => 0, 'floor' => false])
@php
    $currency = session('currency', ['code' => 'KES', 'symbol' => 'KES']);
@endphp
<span {{ $attributes }}>
    {{ $currency['code'] }} {{ $floor ? \App\Support\WalletAmount::format($amount) : number_format($amount, $decimals) }} {{-- 'Coins' --}}
</span>
