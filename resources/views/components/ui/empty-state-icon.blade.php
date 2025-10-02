<div class="flex flex-col justify-items-center items-center">
  <x-ui.icon :name="$icon" size="40" type="duotone" class="text-primary opacity-80" />
  <div class="mt-8 flex flex-col justify-items-center items-center">
    <span class="font-bold text-lg text-center">{{ $title }}</span>
    @isset($subline)
      <span class="font-light text-norma-gray-500 text-sm text-center mt-2">{{ $subline }}</span>
    @endisset
  </div>
</div>
