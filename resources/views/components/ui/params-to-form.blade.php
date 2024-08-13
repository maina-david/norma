@props(['exclude' => []])

@php
  $fromQuery = request()->query();
@endphp

@foreach($fromQuery as $key => $value)
  @if(!in_array($key, $exclude) && !is_null($value))
    @if(is_array($value))
      <select multiple class="hidden" name="{{ $key }}[]">
        @foreach($value as $item)
          <option selected value="{{ $item }}">{{ $item }}</option>
        @endforeach
      </select>
    @else
      <input type="hidden" name="{{ $key }}" value="{{ $value }}" />
    @endif
  @endif
@endforeach
