@props(['value', 'width' => 80, 'noText' => false])

<div class="flex items-center">
  <div class="border border-primary" style="width:{{ $width }}px;">
    <div class="h-1 bg-primary" style="width:{{ (int) (($value / 5) * $width) }}px"></div>
  </div>
  @if (!$noText)
    <div class="ml-2 text-xs text-libryo-gray-600">{{ number_format($value, 2) }}</div>
  @endif
</div>
