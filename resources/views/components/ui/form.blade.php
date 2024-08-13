<form {{ $attributes }} @if ($method) method="{{ in_array(strtolower($method), ['post', 'get']) ? $method : 'POST' }}" @endif @if ($action) action="{{ $action }}" @endif>
  @if (!in_array($method, ['', 'get']))
    {{ csrf_field() }}
  @endif

  {{ $slot }}

  @isset($footer)
    <div class="flex justify-between mt-8">
      {{ $footer ?? '' }}
    </div>
  @endisset
  @if (!in_array(strtolower($method), ['post', 'get']))
    @method($method)
  @endif
</form>
