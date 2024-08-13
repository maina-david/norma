@php use App\Enums\Compilation\ContextQuestionCategory; @endphp
<div class="flex flex-col justify-center mr-3 whitespace-nowrap">
  <div>
    <a
        href="{{ route('collaborate.context-questions.show', ['context_question' => $row->id]) }}"
        class="{{ $row->archived_at ? 'text-negative' : 'text-primary' }}"
    >
      {{ $row->question }}
    </a>
  </div>
  <div class="sub-row">
    @if ($row->category_id)
      {{ ContextQuestionCategory::lang()[$row->category_id] }}
    @else
      -
    @endif
  </div>
</div>
