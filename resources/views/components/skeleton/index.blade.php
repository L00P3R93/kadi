@props(['inline' => false])

{{-- A placeholder block for content that is still loading. Size and shape come from the classes
     passed in, e.g. <x-skeleton class="h-4 w-24 rounded" />; `inline` for use inside text.
     Wrap the loading area in <x-skeleton.region> so screen readers hear one "Loading…". --}}
<span {{ $attributes->class(['kadi-skeleton', $inline ? 'inline-block align-middle' : 'block']) }} aria-hidden="true"></span>
