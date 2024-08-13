@if ($isPdf)
  <iframe src="{{ $path }}" class="w-full" height="800" />
@else
  {!! $content !!}
@endif
