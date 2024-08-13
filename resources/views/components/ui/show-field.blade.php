@props(['label', 'value' => null, 'options' => [], 'type' => 'input'])

<div class="{{ $type == 'checkbox' ? 'flex items-center' : 'mt-4' }}">

  @if ($type === 'checkbox')
    <x-ui.boolean :value="$value" />
  @endif

  @if (!empty($label))
    <span class="text-sm inline-flex {{ $type === 'checkbox' ? '' : 'mt-4 text-libryo-gray-500' }}">
      <span>{{ $label }}</span> {{ $labelSuffix ?? '' }}
    </span>
  @endif

  <div class="mt-1">
    @if ($type !== 'checkbox')

      @if ($type === 'select')

        @foreach ($options as $key => $optionLabel)
          @if ($key !== '--disabled--')
            <div>
              <x-ui.boolean :value="(is_array($value) && in_array($key, $value)) || $key === $value" />
              <span class="text-sm {{ $type === 'checkbox' ? '' : 'mt-4 text-libryo-gray-500' }}">
                {{ $optionLabel }}
              </span>
            </div>
          @endif
        @endforeach

      @elseif($type === 'date' && $value)
        <x-ui.timestamp :timestamp="$value" />
      @else
        <div>{{ $value ?? ($slot->isEmpty() ? '-' : $slot) }}</div>
      @endif

    @endif
  </div>

</div>
