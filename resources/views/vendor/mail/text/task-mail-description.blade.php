@if (!empty($introLine))
{!! $introLine !!}
@endif

@if (!empty($main))
{!! $main !!}
@endif

{{ $slot }}
