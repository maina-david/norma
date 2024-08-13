@php
  use App\Enums\Corpus\WorkStatus;
  use App\Models\Corpus\WorkExpression;
  $allExpressions = WorkExpression::where('work_id', $expression->work_id)->get(['id', 'start_date']);
@endphp

<div class="flex justify-between items-center space-x-2 pr-4 pl-2">
  <div class="flex items-center space-x-2 flex-grow">
    <x-ui.dropdown>
      <x-slot:trigger>
        <button class="rounded-full border-primary p-2">
          <x-ui.icon name="circle-chevron-down"/>
        </button>
      </x-slot:trigger>

      <div class="py-2 text-sm font-normal flex flex-col">
        @if(!request()->routeIs('collaborate.annotations.index'))
          @can('annotate', [$expression, $task])
            <a
              class="px-6 py-1 whitespace-nowrap hover:text-primary"
              target="_top"
              href="{{ route('collaborate.annotations.index', ['expression' => $expression->id, 'task' => $task?->id]) }}"
            >{{ __('corpus.work_expression.annotate') }}</a>
          @endcan
        @endif

        @if(!request()->routeIs('collaborate.annotations.preview'))
          <a
            class="px-6 py-1 whitespace-nowrap hover:text-primary"
            target="_top"
            href="{{ route('collaborate.annotations.preview', $expression->id) }}"
          >{{ __('corpus.work_expression.annotate_preview') }}</a>
        @endif


      @if(!request()->routeIs('collaborate.expressions.creation.index') && !$expression->doc_id)
          @can('bespokeReferences', [$expression, $task])
            <a
              class="px-6 py-1 whitespace-nowrap hover:text-primary"
              target="_top"
              href="{{ route('collaborate.expressions.creation.index', ['expression' => $expression->id, 'task' => $task?->id]) }}"
            >
              {{ __('corpus.work_expression.bespoke_references') }}
            </a>
          @endcan
        @endif

      {{-- remove && $expression->doc_id once everything is moved over to August content model --}}
      @if(!request()->routeIs('collaborate.expressions.identify.index') && $expression->doc_id)
          @can('identifyRequirements', [$expression, $task])
            <a
              class="px-6 py-1 whitespace-nowrap hover:text-primary"
              target="_top"
              href="{{ route('collaborate.expressions.identify.index', ['expression' => $expression->id, 'task' => $task?->id]) }}"
            >
              {{ __('corpus.work_expression.identify_citations') }}
            </a>
          @endcan
        @endif

        @if(!request()->routeIs('collaborate.document-editor.index'))
          @can('editDocument', [$expression, $task])
            <a
              class="px-6 py-1 whitespace-nowrap hover:text-primary"
              target="_top"
              href="{{ route('collaborate.document-editor.index', ['expression' => $expression->id, 'task' => $task?->id]) }}"
            >{{ __('corpus.work_expression.edit_document') }}</a>
          @endcan
        @endif

        @if(!request()->routeIs('collaborate.toc.index'))
          @can('editToC', [$expression, $task])
            <a
              class="px-6 py-1 whitespace-nowrap hover:text-primary"
              target="_top"
              href="{{ route('collaborate.toc.index', ['expression' => $expression->id, 'task' => $task?->id]) }}"
            >{{ __('corpus.work_expression.edit_toc') }}</a>
          @endcan
        @endif

        @if(!request()->routeIs('collaborate.works.show'))
          @can('collaborate.corpus.work.view')
            <a
              class="px-6 py-1 whitespace-nowrap hover:text-primary"
              target="_top"
              href="{{ route('collaborate.works.show', $expression->work_id) }}"
            >{{ __('corpus.work_expression.work_details') }}</a>
          @endcan
        @endif

        @if(!request()->routeIs('collaborate.work-expressions.preview'))
            <a
              class="px-6 py-1 whitespace-nowrap hover:text-primary"
              target="_top"
              href="{{ route('collaborate.work-expressions.preview', $expression->id) }}"
            >{{ __('corpus.work_expression.work_preview') }}</a>
        @endif

      </div>
    </x-ui.dropdown>
    <x-corpus.work.my.work-flags size="8" :work="$expression->work" show-multiple-flags/>
    <x-ui.badge small>{{ $expression->work_id }}</x-ui.badge>
    <div class="whitespace-normal flex flex-col">
      <div class="text-base leading-tight">{{ $expression->work->title }}</div>
      <div class="text-xs italic -mt-1 leading-tight">{{ $expression->work->title_translation }}</div>
    </div>
  </div>

  @include('pages.corpus.collaborate.annotations.partials.chain-tasks')
</div>

@if($allExpressions->count() > 1)
  <div class="flex items-center space-x-2 px-4 pb-1">
    @foreach($allExpressions as $expr)
      @if($expression->id === $expr->id)
        <span class="inline-flex rounded-full items-center py-0.5 px-2 text-xs font-medium bg-primary text-white">
          @if($expr->id === $expression->work->active_work_expression_id)
            <x-ui.icon name="lightbulb-on" size="4" class="mr-2" />
          @endif
          <span>
              {{ $expr->id }}
          </span>
        </span>
      @else
        <a target="_top" x-bind:href="window.location.href.replace(/{{ $expression->id }}/, {{ $expr->id }})"
           class="inline-flex rounded-full items-center py-0.5 px-2 text-xs font-medium bg-libryo-gray-300">
          @if($expr->id === $expression->work->active_work_expression_id)
            <x-ui.icon name="lightbulb-on" size="4" class="mr-2" />
          @endif
          <span>
              {{ $expr->id }}
          </span>
        </a>
      @endif
    @endforeach
  </div>
@endif

@if(!WorkStatus::active()->is($expression->work->status))
  <div class="px-4 py-1 text-xs font-normal bg-red-100">
    {!! __('corpus.work.working_on_status', ['status' => WorkStatus::fromValue($expression->work->status)->label()]) !!}
  </div>
@endif
