@php
  use App\Enums\Auth\UserActivityType;
  use App\Enums\Corpus\ReferenceLinkType;use App\Services\Customer\ActiveNormasManager;
  $totalReadWith = $reference->raises_consequence_groups_count + $readWithsCount + $amendmentsCount;
  $singleMode = app(ActiveNormasManager::class)->isSingleMode();

  $canUpdateMetaData = $canUpdateMetaData ?? false;
@endphp


<div class="p-5">
  <div class="grid grid-cols-1 lg:grid-cols-2">
    <x-ui.tabs x-cloak>
      <x-slot name="nav">
        <x-ui.tab-nav class="items-center flex flex-row" name="content">
          <x-ui.icon name="file-alt" class="mr-2"/>
          {{ __('corpus.reference.requirement_details') }}
        </x-ui.tab-nav>
      </x-slot>

      <x-ui.tab-content name="content" class="border-norma-gray-200 lg:border-r lg:mr-4">
        <div class="norma-legislation p-3">
          {!! $reference->htmlContent?->cached_content !!}
          @if (!$reference->childReferences->isEmpty())
            @foreach ($reference->childReferences as $childRef)
              {!! $childRef->htmlContent?->cached_content !!}
            @endforeach
          @endif
        </div>

        @if ($reference->work->source)
          <div class="italic text-norma-gray-600 p-3 text-xs">
            {!! $reference->work->source->source_content !!}
          </div>
        @endif

        @if($canUpdateMetaData)
          <div class="flex space-x-4 items-center font-semibold pt-4 pb-4">
            <a target="_blank" href="{{ route('collaborate.annotations.work.index',['work'=>$reference->work_id,'activate'=>$reference->id]) }}" class="text-primary-lighter">
              <span>{{ __('actions.action_area.see_in_annotations') }}</span>
              <i class="fa fa-arrow-up-right-from-square"></i>
            </a>
          </div>
        @endif

      </x-ui.tab-content>
    </x-ui.tabs>


    <x-ui.tabs x-cloak>
      <x-slot name="nav">
        <x-ui.tab-nav class="items-center flex flex-row" name="summary">
          <x-ui.icon name="sticky-note" class="mr-2"/>
          {{ __('requirements.summary.notes') }}
        </x-ui.tab-nav>

        <x-ui.tab-nav class="items-center flex flex-row" name="read-with">
          <x-ui.icon name="object-union" class="mr-2"/>
          <span>{{ __('requirements.read_withs') }}</span>
          @if($totalReadWith > 0)
            <span class="ml-1">({{ $totalReadWith }})</span>
          @endif
        </x-ui.tab-nav>

        @if ($organisation->translation_enabled && $reference->work?->language_code !== 'eng')
          <x-ui.tab-nav class="items-center flex flex-row" name="translation">
            <x-ui.icon name="language" class="mr-2"/>
            {{ __('corpus.reference.translation') }}
          </x-ui.tab-nav>
        @endif
      </x-slot>

      <x-ui.tab-content name="summary">
        @if(empty($reference->summary->summary_body ?? ''))
          <div class="text-center text-norma-gray-600 pt-8">
            {{ __('requirements.no_notes') }}
          </div>

        @else
          <div
              x-intersect.once="window.axios.post('{{ route('my.user.activities.track') }}', {type: {{ UserActivityType::viewedTabSummary()->value }}, details: {id: {{ $reference->id }}}})"
              class="p-4 wysiwyg-content">
            @if ($organisation->translation_enabled)
              <div class="grid justify-items-end ">
                <div class="w-52">
                  <x-ui.form method="POST"
                             :action="route('my.lookups.translations.translate.summary', [
                             'summary' => $reference->summary->reference_id,
                         ])">
                    <x-requirements.summary.lang-selector name="language"
                                                          @change="$el.closest('form').requestSubmit()"/>
                  </x-ui.form>
                </div>
              </div>
            @endif
            <div class="norma-summary mt-5">
              <turbo-frame id="summary-content-{{ $reference->summary->reference_id }}" target="_top">
                {!! $reference->summary->summary_body !!}
              </turbo-frame>
            </div>
          </div>
        @endif

      </x-ui.tab-content>

      <x-ui.tab-content name="read-with">
        <div>
          @if($totalReadWith === 0)
            <div class="text-center text-norma-gray-600 pt-8">
              {{ __('requirements.no_read_with') }}
            </div>
          @endif

          @if($amendmentsCount > 0)
            <x-ui.collapse title-size="text-base"
                           title="{{ ReferenceLinkType::AMENDMENT->label() }} ({{ $amendmentsCount }})" flat
                           class="border-b border-norma-gray-200">
              <turbo-frame loading="lazy" id="amendments-for-reference-{{ $reference->id }}"
                           src="{{ route('my.amendments.for.reference', ['reference' => $reference->id]) }}">
                <x-ui.skeleton/>
              </turbo-frame>
            </x-ui.collapse>
          @endif


          @if($readWithsCount > 0)
            <x-ui.collapse title-size="text-base"
                           title="{{ ReferenceLinkType::READ_WITH->label() }} ({{ $readWithsCount }})" flat
                           class="border-b border-norma-gray-200">
              <turbo-frame loading="lazy" id="read-withs-for-reference-{{ $reference->id }}"
                           src="{{ route('my.read-withs.for.reference', ['reference' => $reference->id]) }}">
                <x-ui.skeleton/>
              </turbo-frame>
            </x-ui.collapse>
          @endif

          @if($reference->raises_consequence_groups_count > 0)
            <x-ui.collapse title-size="text-base"
                           title="{{ ReferenceLinkType::CONSEQUENCE->label() }} ({{ $reference->raises_consequence_groups_count }})"
                           flat class="border-b border-norma-gray-200">
              <div
                  x-intersect.once="window.axios.post('{{ route('my.user.activities.track') }}', {type: {{ UserActivityType::viewedTabConsequences()->value }}, details: {id: {{ $reference->id }}}})">
                <turbo-frame loading="lazy" id="consequences-for-reference-{{ $reference->id }}"
                             src="{{ route('my.consequences.for.reference', ['reference' => $reference->id]) }}">
                  <x-ui.skeleton/>
                </turbo-frame>
              </div>
            </x-ui.collapse>
          @endif
        </div>
      </x-ui.tab-content>

      @if ($organisation->translation_enabled && $reference->work?->language_code !== 'eng')
        <x-ui.tab-content name="translation">
          <div class="p-4">
            <turbo-frame loading="lazy" id="reference-translation-{{ $reference->id }}"
                         src="{{ route('my.lookups.translations.translate.reference', ['reference' => $reference->id]) }}">
              <x-ui.skeleton/>
            </turbo-frame>
          </div>
        </x-ui.tab-content>
      @endif
    </x-ui.tabs>
  </div>
</div>


<div class="grid lg:grid-cols-12 lg:gap-4 bg-norma-gray-50 border-t border-norma-gray-100 p-5">
  {{-- LEFT SIDE --}}
  <div class="lg:col-span-8">
    <x-ui.tabs x-cloak>
      <x-slot name="nav">
        <x-ui.tab-nav name="topics">
          <x-ui.icon name="tags" class="mr-2"/>
          {{ __('ontology.tag.topics') }}
        </x-ui.tab-nav>

        @ifOrgHasModule('comply')
        @if($reference->assessmentItems->isNotEmpty())
          <x-ui.tab-nav name="assess">
            <x-ui.icon name="clipboard-list" class="mr-2"/>
            {{ __('assess.assessment_item.index_title') }}
          </x-ui.tab-nav>
        @endif
        @endifOrgHasModule

        @if($singleMode)
          <x-ui.tab-nav name="applicability">
            <x-ui.icon name="ballot-check" class="mr-2"/>
            {{ __('settings.nav.applicability') }}
          </x-ui.tab-nav>
        @endif

      @if($norma && $norma->hasActionsModule())
        @if($reference->actionAreas->isNotEmpty())
          <x-ui.tab-nav name="actions">
            <x-ui.icon name="clipboard-list" class="mr-2"/>
            {{ __('actions.action_area.index_title') }}
          </x-ui.tab-nav>
        @endif
      @endif
      </x-slot>

      <x-ui.tab-content name="topics">
        <div class="pt-5">
          @if (!$reference->categoriesForTagging->isEmpty())
            <div class="mt-3 pt-5">
              <x-ontology.categories.category-links-list :categories="$reference->categoriesForTagging"/>
            </div>
          @endif
        </div>
      </x-ui.tab-content>

      @ifOrgHasModule('comply')
      @if($reference->assessmentItems->isNotEmpty())
        <x-ui.tab-content name="assess">
          <div>
            @if (!$reference->categoriesForTagging->isEmpty())
              @include('partials.corpus.reference.my.assessment-item-responses-for-reference')
            @endif
          </div>
        </x-ui.tab-content>
      @endif
      @endifOrgHasModule

      @if($singleMode)
        <x-ui.tab-content name="applicability">
          <div>
            <turbo-frame
                loading="lazy"
                id="applicability-for-{{ $reference->id }}"
                src="{{ route('my.applicability-reference.index', ['reference' => $reference->id]) }}"
            >
              <x-ui.skeleton/>
            </turbo-frame>
          </div>
        </x-ui.tab-content>
      @endif

      @if($norma && $norma->hasActionsModule())
        @if($reference->actionAreas->isNotEmpty())
          <x-ui.tab-content name="actions">
              <div class="bg-white border border-gray-100 shadow">
                @foreach($reference->actionAreas as $action)
                  <div class="p-4 {{ $loop->even ? 'bg-gray-100' : '' }}">
                    <a href="{{ route('my.actions.action-areas.show', $action->id) }}">
                      {{ $action->title }}
                    </a>
                  </div>
                @endforeach
              </div>
          </x-ui.tab-content>
        @endif
      @endif
    </x-ui.tabs>
  </div>

  {{-- RIGHT SIDE --}}
  <div class="lg:col-span-4">
    <x-ui.tabs x-cloak>
      <x-slot name="nav">

        @ifOrgHasModule('tasks')
        <x-ui.tab-nav name="tasks">
          <x-ui.icon name="tasks" class="mr-2"/>
          {{ __('notify.legal_update.tasks') }}
        </x-ui.tab-nav>
        @endifOrgHasModule

        @ifOrgHasModule('comments')
        <x-ui.tab-nav name="comments">
          <x-ui.icon name="comments" class="mr-2"/>
          {{ __('notify.legal_update.comments') }}
        </x-ui.tab-nav>
        @endifOrgHasModule
        {{-- <x-ui.tab-nav class="items-center flex flex-row" name="reminders">
          <x-ui.icon name="clock" class="mr-2" />
          {{ __('nav.reminders') }}
        </x-ui.tab-nav> --}}

      </x-slot>

      @ifOrgHasModule('tasks')
      <x-ui.tab-content name="tasks">
        <div class="p-4">
          <turbo-frame loading="lazy"
                       src="{{ route('my.tasks.tasks.for.related.index', ['relation' => 'reference', 'id' => $reference]) }}"
                       id="tasks-for-reference-{{ $reference->id }}">
            <x-ui.skeleton/>
          </turbo-frame>
        </div>
      </x-ui.tab-content>
      @endifOrgHasModule
      @ifOrgHasModule('comments')
      <x-ui.tab-content name="comments">
        <div
            x-intersect.once="window.axios.post('{{ route('my.user.activities.track') }}', {type: {{ UserActivityType::viewedTabConsequences()->value }}, details: {id: {{ $reference->id }}}})"
            class="p-4">
          <turbo-frame loading="lazy" id="comments-for-reference-{{ $reference->id }}"
                       src="{{ route('my.comments.for.commentable', ['type' => 'reference', 'id' => $reference->id]) }}">
            <x-ui.skeleton/>
          </turbo-frame>
        </div>
      </x-ui.tab-content>
      @endifOrgHasModule
      {{-- <x-ui.tab-content name="reminders">
        <div x-intersect.once="window.axios.post('{{ route('my.user.activities.track') }}', {type: {{ UserActivityType::viewedTabReminders()->value }}, details: {id: {{ $reference->id }}}})"
             class="p-4">

          <turbo-frame loading="lazy"
                       src="{{ route('my.notify.reminders.for.related.index', ['relation' => 'reference', 'id' => $reference->id]) }}"
                       id="reminders-for-reference-{{ $reference->id }}">
            <x-ui.skeleton />
          </turbo-frame>
        </div>
      </x-ui.tab-content> --}}
    </x-ui.tabs>

  </div>
</div>
