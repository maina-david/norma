@props(['tooltip', 'visible', 'target', 'icon'])
<a
    data-tippy-content="{{ $tooltip }}"
    class="{{ $visible ? 'border-primary text-primary' : 'border-transparent' }} tippy h-8 w-8 hover:text-primary flex items-center justify-center border-t-2"
    href="{{ $target }}"
>
  <x-ui.icon :name="$icon" />
</a>
