@props(['name', 'url'])
@php
$clickJs = '';
$turboRef = "''";
if (isset($url)) {
    $turboRef = sprintf('$refs[\'tab-nav-form-%s\'].requestSubmit()', $name);
    $clickJs = "{$turboRef};";
}
@endphp

<a data-name="{{ $name }}"
   x-init="tab === '{{ $name }}' ? {{ $turboRef }} : '';"
   {{ $attributes->merge(['class' => 'relative whitespace-nowrap py-4 px-2 border-b-2 font-medium text-sm transition-colors transition ease-in-out duration-200 cursor-pointer']) }}
   @click="{{ $clickJs }} tab = '{{ $name }}';"
   x-bind:class="tab == '{{ $name }}' ? 'border-primary text-primary' : 'border-transparent text-norma-gray-500 hover:border-primary hover:text-primary'">
  {{ $slot }}
  @if (isset($url))
    <turbo-frame>
      <x-ui.form x-ref='tab-nav-form-{{ $name }}' method="get" action="{{ $url }}"></x-ui.form>
    </turbo-frame>
  @endif
</a>
