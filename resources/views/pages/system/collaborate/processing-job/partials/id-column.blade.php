<div class="flex flex-col justify-center mr-3 whitespace-nowrap">
  <div>{{ \App\Enums\System\ProcessingJobType::lang()[$row->job_type] }}</div>
  <div class="sub-row">

    @if ($row->catalogue_work_id)
      <div>{{ $row->catalogue_work_id }}</div>
    @endif

    @if ($row->catalogue_work_expression_id)
      <div>{{ $row->catalogue_work_expression_id }}</div>
    @endif

    @if ($row->work)
      <div>{{ $row->work->title }}</div>
    @endif

    @if ($row->workExpression && $row->workExpression->work)
      <div>{{ $row->workExpression->work->title }}</div>
    @endif

    <div class="text-norma-gray-700">{{ \App\Enums\System\ProcessingJobStatus::lang()[$row->status] ?? $row->status }}</div>

    <div>{{ $row->id }}</div>

  </div>
</div>
