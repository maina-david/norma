<div>
  <div class="mt-4 font-semibold text-lg">
    {{ __('compilation.context_question.requirement_reason') }}
  </div>

  <div class="mt-4 mb-8">
    <div class="text-norma-gray-800 font-semibold">{{ __('compilation.context_question.requirement_reason_location', ['norma' => $norma->title]) }}</div>
    @foreach($locations as $location)
      <div class="mt-2">{{ $location }}</div>
    @endforeach
  </div>
  @if(!$questions->isEmpty())
  <div class="mt-4 mb-8">
    <div class="text-norma-gray-800 font-semibold mb-2">{{ __('compilation.context_question.requirement_reason_context') }}</div>

    @if(auth()->user()->canManageApplicability())
      @foreach($questions as $question)
        <a target="_top" href="{{ route('my.context-questions.show', ['question' => $question->hash_id]) }}" class="text-primary">
          {{ $question->question }}
        </a>
      @endforeach
    @else
      @foreach($questions as $question)
        <div class="text-primary mt-2">{{ $question->question }}</div>
      @endforeach
    @endif
  </div>
  @endif


  <div class="mt-4 mb-8">
    <div class="text-norma-gray-800 font-semibold mb-2">{{ __('assess.categories') }}</div>

    @if($categories->isEmpty())
      <div>{{ __('compilation.context_question.no_categories') }}</div>
    @else
      @foreach($categories as $category)
        <div class="mt-2">{{ $category->title }}</div>
      @endforeach
    @endif
  </div>

{{--  <div class="mt-4">--}}
{{--    <div class="font-semibold">{{ __('compilation.context_question.requirement_reason_context_notes') }}</div>--}}
{{--    <div class="text-primary">{{ $notes }}</div>--}}
{{--  </div>--}}
</div>
