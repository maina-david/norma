<div>
  <div class="grid grid-cols-1 gap-10 lg:grid-flow-col-dense lg:grid-cols-12 ">
    <div class="space-y-6 lg:col-span-7">
      <x-ui.tabs x-cloak>
        <x-slot name="nav">
          <x-ui.tab-nav name="requirements" class="relative">
            <x-ui.icon name="gavel" class="mr-2" />
            {{ __('corpus.reference.requirements') }}
            @if ($question->references_count)
              <x-ui.badge small class="absolute top-1 -right-2.5">{{ $question->references_count }}</x-ui.badge>
            @endif
          </x-ui.tab-nav>

          @if($norma->hasAssessModule())
          <x-ui.tab-nav name="assess">
            <x-ui.icon name="clipboard-list" class="mr-2" />
            {{ __('nav.assessment_items') }}
            @if ($assessmentItemsCount)
              <x-ui.badge small class="absolute top-1 -right-2.5">{{ $assessmentItemsCount }}</x-ui.badge>
            @endif
          </x-ui.tab-nav>
          @endif

          @if($norma->hasActionsModule())
          <x-ui.tab-nav name="actions">
            <x-ui.icon name="clipboard-list-check" class="mr-2" />
            {{ __('nav.action_areas') }}
            @if ($actionsCount)
              <x-ui.badge small class="absolute top-1 -right-2.5">{{ $actionsCount }}</x-ui.badge>
            @endif
          </x-ui.tab-nav>
          @endif

          <x-ui.tab-nav name="activities">
            <x-ui.icon name="analytics" class="mr-2" />
            {{ __('assess.activity') }}
            @if ($question->activities_count)
              <x-ui.badge small class="absolute top-1 -right-2.5">{{ $question->activities_count }}</x-ui.badge>
            @endif
          </x-ui.tab-nav>
        </x-slot>
        <div class="mt-5">
          <x-ui.tab-content name="requirements">
            <turbo-frame
              src="{{ route('my.references.for.context-questions.index', ['question' => $question, 'norma' => $norma]) }}"
              loading="lazy"
              id="requirements-for-context-question-{{ $question->id }}"
            >
              <x-ui.skeleton />
            </turbo-frame>
          </x-ui.tab-content>

          @if($norma->hasAssessModule())
          <x-ui.tab-content name="assess">
            <turbo-frame
              src="{{ route('my.assessment-items.for.context-questions.index', ['question' => $question, 'norma' => $norma]) }}"
              loading="lazy"
              id="assessment-for-context-question-{{ $question->id }}"
            >
              <x-ui.skeleton />
            </turbo-frame>
          </x-ui.tab-content>
          @endif

          @if($norma->hasActionsModule())
          <x-ui.tab-content name="actions">
            <turbo-frame
              src="{{ route('my.action-areas.for.context-questions.index', ['question' => $question, 'norma' => $norma]) }}"
              loading="lazy"
              id="action-areas-for-context-question-{{ $question->id }}"
            >
              <x-ui.skeleton />
            </turbo-frame>
          </x-ui.tab-content>
          @endif

          <x-ui.tab-content name="activities">
            <turbo-frame
                src="{{ route('my.activities.for.context-questions.index', ['question' => $question, 'norma' => $norma]) }}"
                loading="lazy"
                id="activities-for-context-question-{{ $question->id }}"
            >
              <x-ui.skeleton />
            </turbo-frame>
          </x-ui.tab-content>
        </div>
      </x-ui.tabs>
    </div>

    <div class="lg:col-span-5">
      <x-ui.tabs x-cloak>
        <x-slot name="nav">
          @ifOrgHasModule('tasks')
          <x-ui.tab-nav name="tasks" class="relative">
            <x-ui.icon name="tasks" class="mr-2" />
            {{ __('my.nav.tasks') }}
            @if ($question->tasks_count)
              <x-ui.badge small class="absolute top-1 -right-2.5">{{ $question->tasks_count }}
              </x-ui.badge>
            @endif
          </x-ui.tab-nav>
          @endifOrgHasModule

          @ifOrgHasModule('comments')
          <x-ui.tab-nav name="comments" class="relative">
            <x-ui.icon name="comments" class="mr-2" />
            {{ __('comments.comments') }}
            @if ($question->comments_count)
              <x-ui.badge small class="absolute top-1 -right-2.5">{{ $question->comments_count }}
              </x-ui.badge>
            @endif
          </x-ui.tab-nav>
          @endifOrgHasModule

        </x-slot>

        <div class="mt-5">

          @ifOrgHasModule('tasks')
          <x-ui.tab-content name="tasks">
            <turbo-frame
              loading="lazy"
              src="{{ route('my.tasks.for.context-questions.index', ['question' => $question->id, 'norma' => $norma->id]) }}"
              id="tasks-for-context-question-{{ $question->id }}"
            >
              <x-ui.skeleton />
            </turbo-frame>
          </x-ui.tab-content>
          @endifOrgHasModule

          @ifOrgHasModule('comments')
          <x-ui.tab-content name="comments">
            <turbo-frame
              src="{{ route('my.comments.for.context-questions.index', ['question' => $question->id, 'norma' => $norma->id]) }}"
              loading="lazy" id="comments-for-context-question-{{ $question->id }}"
            >
              <x-ui.skeleton />
            </turbo-frame>
          </x-ui.tab-content>
          @endifOrgHasModule

        </div>

      </x-ui.tabs>
    </div>
  </div>
</div>
