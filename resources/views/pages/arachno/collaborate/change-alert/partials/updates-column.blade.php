@php
  $expression = $row->work?->expressions->where('doc_id', $row->doc_id)->first();
@endphp
@if ($row->work && $expression)
  <div class="flex flex-col justify-center mr-3 whitespace-nowrap">
    <a class="text-primary"
       href="{{ route('collaborate.expressions.identify.index', ['expression' => $expression]) }}">{{ __('arachno.change_alert.identify_updates') }}</a>
  </div>
@endif
