<div>
  <div class="space-x-2">
    <span>{{ $row->title }}</span>
    @if($row->title_translation)
      <span>({{ $row->title_translation }})</span>
    @endif
  </div>
  <div class="text-xs text-libryo-gray-400 italic">
    <div>{{ $row->primaryLocation->title ?? '' }}</div>
    <div>
      {{ \App\Enums\Corpus\WorkType::lang()[$row->work_type] ?? $row->work_type }}:
      {{ \App\Enums\Corpus\WorkStatus::lang()[$row->status] ?? '' }}
    </div>
  </div>
</div>
