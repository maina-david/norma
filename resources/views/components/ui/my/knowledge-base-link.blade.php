@if ($link)
  <div class="my-5">
    <div class="text-lg">
      {{ __('help.suggested_article') }}
    </div>
    <a class="text-sm cursor-pointer text-primary hover:text-primary-darker" target="_blank"
       href="{{ $link }}">{{ $linkText }}</a>
  </div>
  <div class="text-lg mb-3">
    {{ __('interface.or') }}
  </div>
@endif
