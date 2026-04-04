@props([
    'title',
    'description',
])

<div class="flex w-full flex-col text-center gap-1">
    <flux:heading size="xl" class="!text-[#f5f5f0]" style="font-family: 'Cinzel', serif;">{{ $title }}</flux:heading>
    <flux:subheading class="!text-[#6b6b6b]" style="font-family: 'Outfit', sans-serif;">{{ $description }}</flux:subheading>
</div>
