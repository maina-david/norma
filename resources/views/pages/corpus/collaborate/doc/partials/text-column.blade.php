@if ($row->firstContentResource?->textContentResource)
  <div>
    <a class="text-primary"
       href="{{ $row->firstContentResource->textContentResource->path }}">{{ __('interface.view') }}</a>
  </div>
@endif
