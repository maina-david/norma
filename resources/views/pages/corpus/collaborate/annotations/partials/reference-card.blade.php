@php
  use App\Enums\Corpus\ReferenceStatus;
  use App\Enums\Corpus\ReferenceType;
  if ($active) {
      $reference->load([
        'linkedParents.refPlainText',
        'linkedChildren.refPlainText',
        'linkedParents.work:id,title',
        'linkedChildren.work:id,title',
      ]);
  }
  $payload = ['expression' => $expression->id, 'reference' => $reference->id, 'page' => request('page', 1)];
@endphp
<div
     class="rfr w-full{{ $active ? ' active' : '' }}"
     style="padding-left:{{ $reference->level }}rem;"
     id="reference_{{ $reference->id }}">

  <div
       class="w-full rounded border shadow-sm bg-white {{ ReferenceStatus::pending()->is($reference->status) ? 'border-secondary' : 'border-norma-gray-200' }}">
    <div class="rounded text-sm font-semibold pr-4">
      <div
           class="flex items-center"
           @if (ReferenceType::citation()->is($reference->type)) x-data="{selectors: {{ json_encode($reference->refSelector?->selectors ?? []) }} }"
          @click="function () { evaluateSelector($el, this.selectors) }"
          data-volume="{{ $reference->volume }}" @endif>
        <a
           @click="toggleOpenRef($el)"
           href="{{ route('collaborate.work-expressions.references.' . ($active ? 'deactivate' : 'activate'), $payload) }}"
           class="flex-grow py-2 px-4 mr-4">
          <span class="flex-grow text-primary">
            {{ $reference->refPlainText?->plain_text }}
          </span>
        </a>

        @include('partials.corpus.reference.collaborate.reference-icons')
      </div>


      <div id="meta_{{ $reference->id }}_container">
        @include('pages.corpus.collaborate.annotations.partials.reference-card-meta')
      </div>
      <div id="meta_{{ $reference->id }}_attach" class="ref-meta"></div>
    </div>

    @if ($active)
      <div class="border-t py-2 py-2 px-6">
        @include('pages.corpus.collaborate.annotations.partials.reference-card-buttons')

        <div class="mt-2">
          <x-ui.tabs>
            <x-slot name="nav">

              <x-ui.tab-nav name="related">{{ __('corpus.reference.related') }}</x-ui.tab-nav>

              @if (ReferenceType::citation()->is($reference->type) && !empty($reference->htmlContent?->cached_content))
                <x-ui.tab-nav name="text">{{ __('corpus.reference.text') }}</x-ui.tab-nav>
              @endif

              @if (ReferenceType::citation()->is($reference->type))
                <x-ui.tab-nav name="summary">{{ __('corpus.reference.summary') }}</x-ui.tab-nav>

                @if ($reference->refRequirement || $reference->requirementDraft)
                  <x-ui.tab-nav name="statements">{{ __('corpus.reference.requirement.requirement') }}</x-ui.tab-nav>
                @endif

                @if ($reference->has_consequences ||
                    $reference->has_consequences_update ||
                    $reference->consequences->isNotEmpty())
                  <x-ui.tab-nav name="consequences">{{ __('corpus.reference.consequences') }}</x-ui.tab-nav>
                @endif

              @endif
            </x-slot>

            <x-ui.tab-content name="related">
              @include('pages.corpus.collaborate.annotations.partials.reference-related')
            </x-ui.tab-content>

            @if (ReferenceType::citation()->is($reference->type))
              <x-ui.tab-content name="summary">
                <x-turbo::frame
                               :id="[$reference, 'summary']"
                               :src="route('collaborate.corpus.summary.show', ['reference' => $reference->id])">
                  <x-ui.skeleton />
                </x-turbo::frame>
              </x-ui.tab-content>

              @if ($reference->refRequirement || $reference->requirementDraft)
                <x-ui.tab-content name="statements">
                  @include('pages.corpus.collaborate.annotations.partials.reference-requirement')
                </x-ui.tab-content>
              @endif

              @if ($reference->has_consequences ||
                  $reference->has_consequences_update ||
                  $reference->consequences->isNotEmpty())
                <x-ui.tab-content name="consequences">
                  @include('pages.corpus.collaborate.annotations.partials.reference-consequences')
                </x-ui.tab-content>
              @endif

              @if (!empty($reference->htmlContent?->cached_content))
                <x-ui.tab-content name="text">
                  <div class="pt-4 pb-2">
                    <x-ui.collaborate.wysiwyg-content :content="$reference->htmlContent?->cached_content" />
                  </div>
                </x-ui.tab-content>
              @endif
            @endif


          </x-ui.tabs>
        </div>

        @if ($task ?? false)
          <div class="mt-10">
            <div class="flex items-center mb-4">
              <x-ui.icon name="comments" />
              <span class="ml-3 mr-1 font-semibold">{{ __('workflows.task.comments') }}</span>
            </div>

            <x-turbo::frame
                           loading="lazy"
                           :id="[$reference, 'comments']"
                           :src="route('collaborate.comments.index', [
                               'task' => $task->id,
                               'reference' => $reference->id,
                           ])">
              <x-ui.skeleton />
            </x-turbo::frame>
          </div>
        @endif
      </div>
    @endif
  </div>
</div>
