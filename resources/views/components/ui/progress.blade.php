@props(['color' => 'gray', 'percentage' => 0])

<div class="relative pt-1">
  <div class="overflow-hidden h-2 text-xs flex rounded bg-norma-gray-200">
    <div
         style="width: {{ $percentage }}%"
         class="
        shadow-none
        flex flex-col
        text-center
        whitespace-nowrap
        text-white
        justify-center
        bg-{{ $color }}
      ">
    </div>
  </div>
</div>
