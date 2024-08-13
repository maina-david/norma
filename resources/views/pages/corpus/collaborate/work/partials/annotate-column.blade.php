@if (!empty($filters))
  <div class="text-center">
    <a class="text-primary "
       target="_top"
       href="{{ route('collaborate.annotations.work.index', ['work' => $row->id, ...$filters]) }}">{{ __('corpus.work.annotate_with_filters') }}</a>
  </div>
@endif
