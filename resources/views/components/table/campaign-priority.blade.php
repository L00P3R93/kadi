@php
    // $value = campaign.priority, $item = the AdCategory model
    /*
     * Priorities
     * [1 => 'Urgent', 2 => 'High', 3 => 'Medium', 4 => 'Low', 5 => 'Normal']
     */

    $priorities = [
        1 => ['name' => 'Urgent', 'color' => 'purple'],
        2 => ['name' => 'High', 'color' => 'red'],
        3 => ['name' => 'Medium', 'color' => 'green'],
        4 => ['name' => 'Low', 'color' => 'blue'],
        5 => ['name' => 'Normal', 'color' => 'gray'],
    ];

    $priority = $priorities[$value];
    $name = $priority['name'];
    $color = $priority['color'];
@endphp
<span class="inline-flex items-center rounded-full border border-red bg-[#f5c542]/10 px-2.5 py-0.5 font-cinzel text-xs font-bold text-[#f5c542]">
    {{ $name }}
</span>
