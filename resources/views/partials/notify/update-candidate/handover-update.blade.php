@php
  use App\Enums\Corpus\UpdateCandidateActionStatus;
  use App\Enums\Notify\AffectedLegislationType;
@endphp

<div class="px-4 py-2">
  <div class="mb-2 font-semibold text-lg">{{ __('corpus.doc.handover_update') }}</div>
@if($resource->updateCandidate->legislation->isNotEmpty())
  <x-ui.tabs :active="request('activeTab', 'tab_' . $resource->updateCandidate->legislation->first()->id)">

    <x-slot name="nav">
      @foreach($resource->updateCandidate->legislation as $legislation)
        <x-ui.tab-nav name="{{ 'tab_' . $legislation->id }}">
          <div class="w-full max-w-[20rem] overflow-hidden whitespace-nowrap text-ellipsis tippy"
               data-tippy-content="{{ $legislation->title }}">
            {{ $legislation->title }}
          </div>
        </x-ui.tab-nav>
      @endforeach
    </x-slot>

    @foreach($resource->updateCandidate->legislation as $index => $legislation)

      <x-ui.tab-content name="{{ 'tab_' . $legislation->id }}">
        <div class="py-4">
          <div class="text-lg font-semibold">
            <a class="text-primary" href="{{ $legislation->getUsableSourceURL() }}" target="_blank">
              <div>{{ $legislation->title }}</div>
              <div class="text-libryo-gray-500 font-normal text-sm">{{ $legislation->title_translation }}</div>
            </a>
          </div>

          <div class="text-sm flex items-center mt-2">
            <div>{{ __('corpus.doc.legislation_type') }}:</div>
            <div class="ml-1 font-normal">{{ AffectedLegislationType::tryFrom($legislation->type)->label() }}</div>
          </div>

          <div class="border-t border-libryo-gray-200 mt-4"></div>


          @php
            $legHandovers = $legislation->handovers->map->id->all();
            $usableOptions = $actions->filter(fn ($item, $key) => !in_array($key, $legHandovers))->all();
          @endphp

          @if(!empty($usableOptions))
            <x-ui.form
                method="post"
                action="{{ route('collaborate.docs-for-update.handover-updates.sync', ['doc' => $resource->id, 'legislation' => $legislation->id]) }}"
            >
              <x-ui.input
                  name="handover_update[]"
                  type="select"
                  :options="$usableOptions"
                  :label="__('corpus.doc.select_handover_actions')"
                  multiple
              />

              <x-slot name="footer">
                <div></div>
                <div class="-mt-6">
                  <x-ui.button type="submit" theme="primary">{{ __('actions.add') }}</x-ui.button>
                </div>
              </x-slot>
            </x-ui.form>
          @endif

          <div class="border-t border-libryo-gray-200 mt-4"></div>

          @if($legislation->handovers->isNotEmpty())
            <x-ui.form
                id="delete_handover_legislation_{{ $legislation->id }}_form{{ $resource->id }}"
                method="DELETE"
                action="{{ route('collaborate.docs-for-update.handover-updates.store', ['doc' => $resource->id, 'legislation' => $legislation->id]) }}"
                data-action="{{ route('collaborate.docs-for-update.handover-updates.store', ['doc' => $resource->id, 'legislation' => $legislation->id]) }}"
            />

            <x-ui.form
              method="post"
              action="{{ route('collaborate.docs-for-update.handover-updates.store', ['doc' => $resource->id, 'legislation' => $legislation->id]) }}"
            >

              @foreach($legislation->handovers as $handover)

                <div>
                  <x-ui.input
                      name="action{{ $handover->id }}"
                      type="select"
                      :options="UpdateCandidateActionStatus::forSelector()"
                      :value="$handover->pivot->status"
                      :label="$handover->text"
                      :placeholder="__('auth.user.status')"
                  />

                  @if(in_array('last_checked', $handover->include_meta))
                    <x-ui.input
                        name="meta_action{{ $handover->id }}[last_checked]"
                        type="date"
                        :options="UpdateCandidateActionStatus::forSelector()"
                        :value="$handover->pivot->meta['last_checked'] ?? null"
                        :label="__('corpus.doc.last_checked')"
                        :placeholder="__('corpus.doc.last_checked')"
                    />
                  @endif

                </div>

                <div class="flex justify-end mt-4 space-x-2">

                  @if($handover->pivot->task_id)
                    <x-ui.button
                        type="link"
                        styling="outline"
                        theme="primary"
                        @click="open = true"
                        href="{{ route('collaborate.tasks.show', ['task' => $handover->pivot->task_id]) }}"
                    >
                      {{ __('tasks.view_task') }}
                    </x-ui.button>

                  @elseif($handover->board)
                    @can('collaborate.workflows.create-from-doc')
                      <div>
                        @include('partials.notify.update-candidate.generate-workflow-button')
                      </div>
                    @endcan
                  @endif


                  <x-ui.button
                      styling="outline"
                      theme="secondary"
                      @click="document.querySelector('#delete_handover_legislation_{{ $legislation->id }}_form{{ $resource->id }}').setAttribute('action', document.querySelector('#delete_handover_legislation_{{ $legislation->id }}_form{{ $resource->id }}').getAttribute('data-action') + '/{{ $handover->id }}');document.querySelector('#delete_handover_legislation_{{ $legislation->id }}_form{{ $resource->id }}').requestSubmit()"
                  >
                    {{ __('actions.delete') }}
                  </x-ui.button>
                </div>

              @endforeach

              <x-ui.textarea
                  name="handover_comments"
                  wysiwyg="basic"
                  :label="__('comments.comments')"
                  :value="old('handover_comments', $legislation->handover_comments ?? null)"
              />

              <x-slot name="footer">
                <div></div>
                <div>
                  <x-ui.button type="link" theme="secondary" @click="open = false"
                               href="{{ route('collaborate.docs-for-update.affected.index', ['doc' => $resource->id]) }}">
                    {{ __('actions.close') }}
                  </x-ui.button>

                  <x-ui.button type="submit" theme="primary">
                    {{ __('actions.save') }}
                  </x-ui.button>
                </div>
              </x-slot>
            </x-ui.form>

          @endif


        </div>
      </x-ui.tab-content>
    @endforeach

  </x-ui.tabs>
@endif
</div>
