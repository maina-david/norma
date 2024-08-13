<div class="flex items-center">
  @if ($row->work->active_work_expression_id !== $row->id)
    @can('collaborate.corpus.work-expression.activate')
      <x-ui.confirm-button
                           method="PUT"
                           :route="route('collaborate.work-expressions.activate', ['expression' => $row->id])"
                           :label="__('collaborators.activate')"
                           :confirmation="__('corpus.work_expression.activate_confirmation')">
        {{ __('collaborators.activate') }}
      </x-ui.confirm-button>
    @endcan
  @endif

  @can('collaborate.corpus.work-expression.identify-without-task')
    <x-ui.button type="link" styling="flat" theme="primary"
                 href="{{ route('collaborate.expressions.identify.index', ['expression' => $row->id]) }}" target="_top">
      {{ __('corpus.work_expression.identify_citations') }}
    </x-ui.button>
  @endcan

  @can('collaborate.corpus.work-expression.annotate-without-task')
    <x-ui.button type="link" styling="flat" theme="primary"
                 href="{{ route('collaborate.annotations.index', ['expression' => $row->id]) }}" target="_top">
      {{ __('corpus.work_expression.annotate') }}
    </x-ui.button>
  @endcan

  <x-ui.button type="link" styling="flat" theme="primary"
               href="{{ route('collaborate.annotations.preview', ['expression' => $row->id]) }}" target="_top">
    {{ __('corpus.work.annotation_preview') }}
  </x-ui.button>

  @if ($row->doc_id)
    <x-ui.button type="link" styling="flat" theme="primary"
                 href="{{ route('collaborate.corpus.docs.preview', ['doc' => $row->doc_id]) }}" target="_top">
      {{ __('corpus.work_expression.doc_preview') }}
    </x-ui.button>
  @endif

</div>
