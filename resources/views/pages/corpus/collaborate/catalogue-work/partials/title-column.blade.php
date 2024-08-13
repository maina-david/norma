<div>
  <div>
    {{ $row->title }}
  </div>
  <div class="">
    @foreach ($row->expressions as $expression)
      <div><a class="cursor-pointer text-primary" href="{{ route('collaborate.catalogue-work-expressions.show', ['expression' => $expression->id]) }}">{{ $expression->start_date }}</a></div>
    @endforeach
  </div>
</div>
