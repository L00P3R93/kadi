@props(['label' => __('Loading…')])

{{-- Marks a loading area for assistive tech; the skeleton blocks inside are aria-hidden. --}}
<div {{ $attributes }} role="status" aria-busy="true" aria-live="polite" data-test="skeleton">
    <span class="sr-only">{{ $label }}</span>
    {{ $slot }}
</div>
