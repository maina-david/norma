@props(['titleSize' => 'text-lg', 'show' => false, 'flat' => false, 'icon', 'title'])
<div x-data="{ showContent: {{ $show ? 'true' : 'false' }} }" {{ $attributes->merge(['class' => $flat ? 'overflow-hidden' : 'bg-white shadow overflow-hidden sm:rounded-lg']) }}>
  <div @click="showContent = !showContent" class=" cursor-pointer px-4 py-5 sm:px-6 flex flex-row items-center justify-between">
    <h3 class="{{ $titleSize }} leading-6 font-medium text-libryo-gray-900">
      @if($icon ?? false)
        <x-ui.icon :name="$icon" class="mr-5" />
      @endif

      {{ $title }}
    </h3>
    <div>
      <x-ui.icon x-show="!showContent" name="chevron-down" size="3" />
      <x-ui.icon x-show="showContent" name="chevron-up" size="3" />
    </div>
  </div>
  <div x-show="showContent" class="border-t border-libryo-gray-200 px-4 py-5 sm:p-0">
    <div class="p-6">
      {{ $slot }}
    </div>
  </div>
</div>
