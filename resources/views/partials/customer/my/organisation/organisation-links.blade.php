@foreach ($organisations as $organisation)
  <a class="mx-2 text-primary" href="">{{ $organisation->title }}</a>
  @if (!$loop->last)
    <span class="mx-2">&bull;</span>
  @endif
@endforeach
