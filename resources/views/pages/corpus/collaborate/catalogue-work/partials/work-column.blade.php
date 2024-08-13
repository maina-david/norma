<div>
  @if ($row->work)
    @can('collaborate.corpus.work.view')
    <a class="text-primary" href="{{ route('collaborate.works.show', ['work' => $row->work->id]) }}">{{ __('corpus.catalogue_work.view_work') }}</a>
    @endcan
  @endif
</div>
