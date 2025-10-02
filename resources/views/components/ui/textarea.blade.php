@props(['name', 'label' => null, 'required' => false, 'value', 'wysiwyg' => null])
{{-- wysiwyg can either be "full" or "basic" --}}

@if($label)
  <x-ui.label :for="$name" class="mt-4 mb-1">
    @if($required)
      <span class="text-red-400">*</span>
    @endif
    {{ $label }}
  </x-ui.label>
@endif

<textarea
  id="{{ $name }}"
  name="{{ $name }}"
  {{ $required && !$wysiwyg ? 'required' : '' }}
  {{ $attributes->merge(['rows' => 8, 'class' => ($wysiwyg ? "norma-editor-{$wysiwyg} " : '') . 'px-3 py-2 border leading-normal rounded-md shadow-sm border-norma-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 block mt-1 w-full' ]) }}
>{{ old($name, $value ?? '') }}</textarea>

@error($name)
<div>
  <span class="text-sm text-red-400">{{ $message }}</span>
</div>
@enderror

