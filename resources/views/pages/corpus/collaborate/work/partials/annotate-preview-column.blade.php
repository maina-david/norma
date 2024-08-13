@if($row->active_work_expression_id)
  <div class="text-center">
    <a
        class="text-primary"
        href="{{ route('collaborate.annotations.preview', ['expression' => $row->active_work_expression_id]) }}"
        target="_top"
    >
      {{ __('corpus.work.annotation_preview') }}
    </a>
  </div>

@endif
