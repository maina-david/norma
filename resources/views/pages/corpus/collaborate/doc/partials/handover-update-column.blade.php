@php
  use App\Enums\Corpus\UpdateCandidateActionStatus;
  $options = UpdateCandidateActionStatus::lang();
@endphp

<div id="doc-handover-update-{{ $legislation->id }}" class="relative group w-72">
  <div class="grid grid-cols-3 gap-2 text-xs">
    @foreach(($handovers ?? []) as $action)
      <div class="col-span-2 flex {{ UpdateCandidateActionStatus::DONE->value == $action->pivot->status ? 'text-primary' : 'text-secondary' }}">
        <div class="mr-1">-</div>
        <div>{{ $action->text }}</div>
      </div>

      <div class="{{ UpdateCandidateActionStatus::DONE->value == $action->pivot->status ? 'text-primary' : 'text-secondary' }}">
        <div>{{ $options[$action->pivot->status] ?? '-' }}</div>


        @foreach(($action->include_meta ?? []) as $meta)
          @if($action->pivot->meta[$meta] ?? false)
            <div>{{ $action->pivot->meta[$meta] }}</div>
          @endif
        @endforeach
      </div>
    @endforeach
  </div>
</div>

