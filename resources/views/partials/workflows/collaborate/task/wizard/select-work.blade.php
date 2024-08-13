@php
  use App\Enums\Application\ApplicationType;
  use App\Models\Corpus\Work;
  $work = request('work_id');
  $work = $work ? Work::find($work) : null;
@endphp

<div>
  <x-corpus.work-expression.work-expression-selector
      :app-type="ApplicationType::collaborate()"
      :work="$work"
  />

  <div class="px-2 mt-4 font-semibold">
    @error('work_expression_id')
    <div class="text-sm text-red-400">{!! $message !!}</div>
    @enderror
  </div>
</div>
