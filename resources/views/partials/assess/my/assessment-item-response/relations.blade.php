<div>
  @if (isset($showDescription) && $showDescription)
    <div class="font-medium mb-4">
      {{ $assessmentItem->toDescription() }}
    </div>
  @endif

  <div class="grid grid-cols-1 gap-10 lg:grid-flow-col-dense lg:grid-cols-12 ">
    <div class="space-y-6 lg:col-span-7">
      <x-ui.tabs x-cloak>
        <x-slot name="nav">
          <x-ui.tab-nav name="requirements" class="relative">
            <x-ui.icon name="gavel" class="mr-2" />
            {{ __('corpus.reference.requirements') }}
            @if ($assessmentItem->references_count)
              <x-ui.badge small class="absolute top-1 -right-2.5">{{ $assessmentItem->references_count }}
              </x-ui.badge>
            @endif
          </x-ui.tab-nav>

          @ifOrgHasModule('drives')
          <x-ui.tab-nav name="files" class="relative">
            <x-ui.icon name="folder-open" class="mr-2" />
            {{ __('storage.file.files') }}
            @if ($assessmentItemResponse->files_count)
              <x-ui.badge small class="absolute top-1 -right-2.5">{{ $assessmentItemResponse->files_count }}
              </x-ui.badge>
            @endif
          </x-ui.tab-nav>
          @endifOrgHasModule

          <x-ui.tab-nav name="activities">
            <x-ui.icon name="analytics" class="mr-2" />
            {{ __('assess.activity') }}
          </x-ui.tab-nav>
        </x-slot>
        <div class="mt-5">
          <x-ui.tab-content name="requirements">
            <turbo-frame src="{{ route('my.references.for.assessment-item-response.index', ['aiResponse' => $assessmentItemResponse]) }}"
                         loading="lazy"
                         id="requirements-for-assessment-item-response-{{ $assessmentItemResponse->id }}">
              <x-ui.skeleton />
            </turbo-frame>
          </x-ui.tab-content>

          @ifOrgHasModule('drives')
          <x-ui.tab-content name="files">
            <turbo-frame loading="lazy"
                         src="{{ route('my.drives.files.for.assessment-item-response.index', ['response' => $assessmentItemResponse]) }}"
                         id="files-for-assessment-item-response-{{ $assessmentItemResponse->id }}">
              <x-ui.skeleton />
            </turbo-frame>
          </x-ui.tab-content>
          @endifOrgHasModule

          <x-ui.tab-content name="activities">
            <turbo-frame loading="lazy"
                         src="{{ route('my.assess.assessment-activities.index.for.response', ['response' => $assessmentItemResponse]) }}"
                         id="activities-for-assessment-item-response-{{ $assessmentItemResponse->id }}">
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
            @if ($assessmentItemResponse->tasks_count)
              <x-ui.badge small class="absolute top-1 -right-2.5">{{ $assessmentItemResponse->tasks_count }}
              </x-ui.badge>
            @endif
          </x-ui.tab-nav>
          @endifOrgHasModule

          @ifOrgHasModule('comments')
          <x-ui.tab-nav name="comments" class="relative">
            <x-ui.icon name="comments" class="mr-2" />
            {{ __('comments.comments') }}
            @if ($assessmentItemResponse->comments_count)
              <x-ui.badge small class="absolute top-1 -right-2.5">{{ $assessmentItemResponse->comments_count }}
              </x-ui.badge>
            @endif
          </x-ui.tab-nav>
          @endifOrgHasModule

        </x-slot>

        <div class="mt-5">

          @ifOrgHasModule('tasks')
          <x-ui.tab-content name="tasks">
            <turbo-frame loading="lazy"
                         src="{{ route('my.tasks.tasks.for.related.index', ['relation' => 'assessment-item-response','id' => $assessmentItemResponse]) }}"
                         id="tasks-for-assessment-item-response-{{ $assessmentItemResponse->id }}">
              <x-ui.skeleton />
            </turbo-frame>
          </x-ui.tab-content>
          @endifOrgHasModule

          @ifOrgHasModule('comments')
          <x-ui.tab-content name="comments">
            <turbo-frame src="{{ route('my.comments.for.commentable', ['type' => 'assessment-item-response','id' => $assessmentItemResponse->id]) }}"
                         loading="lazy" id="comments-for-assessment-item-response-{{ $assessmentItemResponse->id }}">
              <x-ui.skeleton />
            </turbo-frame>
          </x-ui.tab-content>
          @endifOrgHasModule

        </div>

      </x-ui.tabs>
    </div>
  </div>
</div>
