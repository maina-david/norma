<x-layouts.app>
  <x-slot name="header">
    <div class="flex items-center">

      @if($draft)
        <x-ui.button styling="outline" theme="primary" class="rounded-full px-2" type="link" href="{{ route('my.assess.assessment-item-responses.index') }}">
          <x-ui.icon name="chevron-left" :size="2" />
        </x-ui.button>
      @endif

      <x-ui.icon name="clipboard-list" class="mr-3 ml-5 text-norma-gray-400" size="8" />

      <div>
        {{ __('my.nav.assessment_items') }}
        <span class="text-xs text-norma-gray-500 italic ml-3">{{ $subTitle }}</span>
      </div>
    </div>
  </x-slot>
  <x-slot name="actions">
    <div class="flex flex-row justify-between items-center">
      <a target="_blank" href="https://success.norma.com/en/knowledge/norma-assess">
        <x-ui.icon name="question-circle" />
      </a>
    </div>
  </x-slot>

  <div>
    @if($singleMode && !$draft && $pendingCount > 0)
      <x-ui.alert-box type="negative" class="mb-5">
        {!! trans_choice('assess.assessment_item_response.pending_responses', $pendingCount, ['title' => $subTitle, 'number' => $pendingCount]) !!}
        {!! __('assess.assessment_item_response.pending_responses_target', ['target' => route('my.assess.pending')]) !!}
      </x-ui.alert-box>
    @endif
    <x-ui.alert-box class="mb-5">
      {{ $draft ? __('assess.rolling_assessment_draft_info', ['title' => $subTitle]) : __('assess.rolling_assessment_info', ['title' => $subTitle]) }}
    </x-ui.alert-box>

    @if ($singleMode)
      <x-assess.assessment-item-response.response-data-table
                                                             :base-query="$baseQuery"
                                                             :route="route('my.assess.assessment-item-responses.index')"
                                                             :fields="$tableFields"
                                                             searchable
                                                             :search-placeholder="__('assess.assessment_item.search_assessment_items') . '...'"
                                                             filterable
                                                             sticky-headings
                                                             :filters="['domains', 'rating', 'answered', 'controls', 'user-generated', 'answer', 'bookmarked']"
                                                             actionable
                                                             :actions-route="route('my.assess.assessment-item-responses.actions')"
                                                             :actions="$actions"
                                                             :paginate="25">

        <x-slot name="actionButton">
          <div class="flex items-center space-x-4">
            <x-ui.button type="link" styling="outline" theme="primary" href="{{ route('my.assess.create') }}">
              <x-ui.icon size="4" name="plus" />
              <span class="ml-2">{{ __('actions.create') }}</span>
            </x-ui.button>

            @if(!$draft)
              <x-ui.modal>
              <x-slot name="trigger">
                <x-ui.button @click="open = true" theme="primary" styling="outline" class="tippy"
                             data-tippy-content="{{ __('interface.export') }}">
                  <x-ui.icon size="4" name="download" />
                </x-ui.button>
              </x-slot>

              <div class=" w-screen-50">
                <turbo-frame id="download-progress" loading="lazy"
                             src="{{ route('my.assessment-item-responses.responses.export.excel', request()->query()) }}">
                </turbo-frame>

                @if ($filtersApplied)
                  <div class="mt-5 italic px-10 text-center">
                    {{ __('assess.assessment_item_response.export_filter_warning') }}
                  </div>
                @endif
              </div>
            </x-ui.modal>
            @endif

          </div>
        </x-slot>

      </x-assess.assessment-item-response.response-data-table>
    @else
      <x-assess.assessment-item.assessment-item-data-table :base-query="$baseQuery"
                                                           :route="route('my.assess.assessment-item-responses.index')"
                                                           :fields="['description_full', 'applicable_streams']"
                                                           searchable
                                                           :search-placeholder="__('assess.assessment_item.search_assessment_items') . '...'"
                                                           filterable
                                                           :filters="['domains', 'bookmarked']"
                                                           :paginate="25">

        <x-slot name="actionButton">
          <x-ui.modal>
            <x-slot name="trigger">
              <x-ui.button @click="open = true" theme="primary" styling="outline" class="tippy"
                           data-tippy-content="{{ __('interface.export') }}">
                <x-ui.icon name="download" />
              </x-ui.button>
            </x-slot>

            <div class=" w-screen-50">
              <turbo-frame id="download-progress" loading="lazy"
                           src="{{ route('my.assessment-item-responses.responses.export.excel', request()->query()) }}">
              </turbo-frame>

              @if ($filtersApplied)
                <div class="mt-5 italic px-10 text-center">
                  {{ __('assess.assessment_item_response.export_filter_warning') }}
                </div>
              @endif
            </div>
          </x-ui.modal>
        </x-slot>

      </x-assess.assessment-item.assessment-item-data-table>
    @endif

  </div>

</x-layouts.app>
