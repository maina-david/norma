@php
  $isCheckbox = in_array($type, ['checkbox', 'radio']);
@endphp
<div class="{{ $isCheckbox ? 'flex items-center' : '' }}">
  @if ($isCheckbox)
    <input id="{{ $id ?? $name }}" @if ($value) checked @endif name="{{ $name }}"
           type="{{ $type }}"
           value="{{ $checkboxValue }}" {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'mr-2 h-4 w-4 text-primary focus:ring-primary border-libryo-gray-300 rounded']) }} />
  @endif

  @if ($label)
    <x-ui.label :for="$noLabelId ? '' : ($id ?? $name)" class="{{ $isCheckbox ? '' : 'mt-4 flex items-center' }}">
      @if ($required && !$noRequiredAnnotation)
        <span class="text-red-400 mr-1">*</span>
      @endif
      {!! $label !!}
    </x-ui.label>
  @endif

  <div class="mt-1" style="{{ $type === 'select' ? 'min-height:42px;' : '' }}">
    @if (!$isCheckbox)

      @if ($type === 'select')
        <select
          id="{{ $name }}"
          style="{{ $tomselect ? 'visibility:hidden;height:2rem;' : '' }}"
          data-tomselect="{{ $tomselect ? 'true' : 'false' }}"
          name="{{ $name }}"
          placeholder="{{ $placeholder ?? '' }}"
          {{ $attributes->merge(['class' =>'sm:pb-3 block focus:ring-primary focus:border-primary w-full shadow-sm sm:text-sm border-libryo-gray-300 rounded-md']) }}
        >

          @foreach ($options as $key => $optionLabel)
            @if ($key === '--disabled--')
              <option value="" {{ empty($value) ? ' selected' : '' }}>{{ $label }}</option>
            @else
              @php
                $isSelected = is_array($value) ? in_array($key, $value) : $key === $value || ($key !== 0 && empty($key) && empty($value));
              @endphp
              <option
                value="{{ $key }}"
                {{ $isSelected ? ' selected' : '' }}
                data-detail="{{ $optionDetails[$key] ?? '' }}"
              >
                {{ $optionLabel }}
              </option>
            @endif
          @endforeach
        </select>
      @elseif($type === 'textarea')
        <textarea id="{{ $name }}" name="{{ $name }}" type="{{ $type }}"
                  placeholder="{{ $placeholder ?? '' }}"
          {{ $attributes->merge(['class' =>'px-3 py-2 border leading-normal rounded-md shadow-sm border-libryo-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 block mt-1 w-full']) }}
            {{ $required ? 'required' : '' }}>{{ $value }}</textarea>
      @else
        <input value="{{ $value }}" id="{{ $name }}" name="{{ $name }}"
               type="{{ $type }}" placeholder="{{ $placeholder ?? '' }}"
               maxlength="{{ $maxlength ?? '255' }}" {{ $required ? 'required' : '' }}
            {{ $attributes->merge(['class' =>'px-3 py-2 border leading-normal rounded-md shadow-sm border-libryo-gray-300 focus:border-primary focus:ring-primary block mt-1 w-full']) }} />

      @endif

    @endif
  </div>

</div>

@if(!($noError ?? false))
  @error($selectName)
  <div class="text-sm text-red-400">{{ $message }}</div>
  @enderror
@endif
