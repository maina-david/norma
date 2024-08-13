@php
use App\Models\Corpus\Reference;
@endphp
<td class="py-4 px-4 rounded-tl-md" {!! $attributes !!}>
  <div class="text-primary font-semibold">
    @if((!$hasSubRow ?? false) && ($column->href ?? false))
      <a href="{{ $column->clickTarget($row) }}">
        {{ $row->title }}
      </a>
    @else
    {{ $row->title }}
    @endif
  </div>
@if($row->taskable_type === (new Reference)->getMorphClass())
  <div class="text-xs text-libryo-gray-500 mt-1">{{ $row->taskable->refPlainText->plain_text ?? '-' }}</div>
@endif

@if(multi_stream() && $row->libryo)
  <div class="text-xs text-libryo-gray-500 mt-1">{{ $row->libryo->title }}</div>
@endif
</td>
