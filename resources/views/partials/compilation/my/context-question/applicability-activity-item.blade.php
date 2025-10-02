@php
use App\Enums\Compilation\ContextQuestionAnswer;
use App\Models\Auth\User;

$user = $activity->user ?? new User(['fname' => 'Deleted', 'sname' => 'User']);

@endphp
<div class="p-4 border-b border-norma-gray-200 w-full flex items-center">
  <div class="flex-shrink-0">
    <x-ui.user-avatar :user="$user" dimensions="10" />
  </div>

  <div class="flex-grow mx-4">
    <div>
      @if($activity->contextQuestion)
        {!! __('compilation.applicability.activity_context_description', ['user' => $user->full_name, 'question' => $activity->contextQuestion->toQuestion(), 'state' => ContextQuestionAnswer::fromValue($activity->current)?->label(), 'norma' => $activity->norma->title]) !!}
      @elseif($activity->reference)
        {!! __('compilation.applicability.activity_requirement_description', ['user' => $user->full_name, 'action' => $activity->activity_type->label(), 'requirement' => $activity->reference->refPlainText->plain_text ?? '', 'norma' => $activity->norma->title]) !!}
      @endif
    </div>

    <div class="text-norma-gray-600 text-sm">
      {{ $activity->note->comment ?? '-' }}
    </div>
  </div>

  <div class="flex-shrink-0 text-norma-gray-600 text-sm">
    {{ $activity->created_at->format('d M Y') }}
  </div>
</div>
