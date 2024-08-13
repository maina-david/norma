@props(['active' => request('tab')])
@php
preg_match('/data-name="(.+)"/m', $nav, $matches);
$activeTab = $active ?? ($matches[1] ?? null);
@endphp

<div x-data="{ tab: '{{ $activeTab }}' }">
  <input type="hidden" name="_tab" x-model="tab">
  <div class="border-b border-libryo-gray-200">
    <nav class="-mb-px flex space-x-8 w-full overflow-x-auto custom-scroll pb-2">
      {{ $nav }}
    </nav>
  </div>

  <div class="mb-4">
    {{ $slot }}
  </div>
</div>
