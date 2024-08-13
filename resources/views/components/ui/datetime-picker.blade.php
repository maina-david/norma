@props(['value', 'label', 'name', 'type' => 'datetime'])
<div
  class="flex items-end space-x-2"
  x-data="{selectedDate: null, selectedTime: null}"
@if($value)
  x-init="function () {
    var curDate = new Date({{ $value->getTimestamp() * 1000 }});
    this.selectedDate = curDate.toISOString().split('T')[0];
    this.selectedTime = curDate.getHours().toString().padStart(2, '0') + ':' + curDate.getMinutes().toString().padStart(2, '0');
  }"
@endif
>
  <div class="flex-grow">
    <x-ui.input
      x-model="selectedDate"
      type="date"
      name="datetime_selector_date"
      :label="$label"
    />
  </div>

  @if($type === 'datetime')
    <div class="flex-shrink-0">
      <x-ui.input
          x-model="selectedTime"
          type="time"
          name="datetime_selector_time"
          label=""
      />

      <input name="{{ $name }}" type="hidden" x-bind:value="selectedDate && selectedTime ? (new Date(selectedDate + ' ' + selectedTime)).toISOString() || '' : ''">
    </div>
  @else
    <input name="{{ $name }}" type="hidden" x-bind:value="selectedDate ? (new Date(selectedDate + ' 00:00')).toISOString() || '' : ''">
  @endif
</div>
