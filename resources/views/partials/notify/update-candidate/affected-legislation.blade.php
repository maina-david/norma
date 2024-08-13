@php
  use App\Enums\Notify\AffectedLegislationType;
  $types = AffectedLegislationType::forSelector();
  $canLink = auth()->user()->can('collaborate.corpus.work.link-to-doc');
  $canViewWork = auth()->user()->can('collaborate.corpus.work.view');
@endphp
<x-ui.form
  method="post"
  action="{{ route('collaborate.docs-for-update.affected.store', ['doc' => $resource->id]) }}"
  class="p-4"
  x-data="{
    deleted: [],
    selectingWork: false,
    selectingWorkLoaded: false,
    selectingDoc: false,
    selectingDocLoaded: false,
    onSelect: null,
    startCount: {{ count($resource->updateCandidate->legislation ?? []) }},
    legislation: [],
    updateFields: function (element, type, details) {
      var routes = {
        'work-id': '{{ route('collaborate.works.show', ['work' => 0]) }}',
        'catalogue-doc-id': '{{ route('collaborate.corpus.catalogue-docs.docs.index', ['catalogueDoc' => 0]) }}',
      };
      var parent = element.closest('.legislation-group');
      var title = parent.querySelector('.' + type + '-input');
      parent.querySelector('.' + type + '-input .doc-title').innerHTML = details.title;
      parent.querySelector('.' + type + '-input .doc-title-translation').innerHTML = details.title_translation;
      parent.querySelector('.' + type).value = details.id;
      var legTitle = parent.querySelector('.title-input');
      var legTitleTranslation = parent.querySelector('.title-translation-input');
      var legUrl = parent.querySelector('.url-input');

      setTimeout(function () {
        title.scrollIntoView();
      }, 100);

      if (type === 'catalogue-doc-id') {
        legUrl.value = routes[type].replace(/\/0/, '/' + details.id);
        legTitle.value = details.title;
        legTitleTranslation.value = details.title_translation;
      }

      if (type === 'catalogue-doc-id' && details.work && details.work.id) {
        this.updateFields(element, 'work-id', details.work);
      }
    },
  }"
>
  <div class="-mb-8">
    <div x-show="selectingWork || selectingDoc" class="overflow-hidden" style="max-height:90vh">
      <template x-if="selectingWorkLoaded">
        <div x-show="selectingWork">
          <div class="mb-2 pl-2 text-lg">{{ __('corpus.work.select_work') }}</div>
          <work-selector initial-location="{{ $resource->primary_location_id }}" x-on:select="function (event) { if (onSelect) { onSelect(event.detail[0], 'work-id'); } selectingWork = false; }" style="max-height:70vh;min-height:50vh;" class="h-full" />
        </div>
      </template>

      <template x-if="selectingDocLoaded">
        <div x-show="selectingDoc">
          <div class="mb-2 pl-2 text-lg">{{ __('corpus.work.select_catalogue_doc') }}</div>
          <catalogue-doc-selector initial-location="{{ $resource->primary_location_id }}" x-on:select="function (event) { if (onSelect) { onSelect(event.detail[0], 'catalogue-doc-id'); } selectingDoc = false; }" style="max-height:70vh;min-height:50vh;" class="h-full" />
        </div>
      </template>

      <div class="flex justify-end mt-2">
        <x-ui.button styling="outline" theme="secondary" @click="function () { selectingWork = false; selectingDoc = false; }">
          {{ __('actions.cancel') }}
        </x-ui.button>
      </div>
    </div>

    <input type="hidden" name="deleted_legislation" x-bind:value="deleted">

    <div x-show="!selectingWork && !selectingDoc">
      <div class="text-semibold text-sm">{{ __('corpus.doc.affected_legislation') }}</div>

      @foreach($resource->updateCandidate->legislation as $index => $item)
        <div class="border-b border-libryo-gray-200 pb-4 group legislation-group" x-bind:key="{{ $index }}">
          <input type="hidden" name="affected_legislation[{{ $index }}][id]" value="{{ $item->id }}">
          <input type="hidden" class="work-id" name="affected_legislation[{{ $index }}][work_id]" value="{{ $item->work_id }}">
          <input type="hidden" class="catalogue-doc-id" name="affected_legislation[{{ $index }}][catalogue_doc_id]" value="{{ $item->catalogue_doc_id }}">

          <x-ui.input
            :tomselect="false"
            required
            type="select"
            :options="$types"
            :label="__('corpus.doc.legislation_type')"
            :value='old("affected_legislation.{$index}.type", $item["type"])'
            name="affected_legislation[{{ $index }}][type]"
          ></x-ui.input>

          <x-ui.input
            required
            :label="__('interface.title')"
            :value='old("affected_legislation.{$index}.title", $item["title"])'
            name="affected_legislation[{{ $index }}][title]"
            maxlength="1000"
            class="title-input"
          ></x-ui.input>

          <x-ui.input
            :label="__('corpus.work.title_translation')"
            :value='old("affected_legislation.{$index}.title_translation", $item["title_translation"])'
            name="affected_legislation[{{ $index }}][title_translation]"
            maxlength="1000"
            class="title-translation-input"
          ></x-ui.input>

          <x-ui.input
            required
            :label="__('corpus.work.source_url')"
            :value='old("affected_legislation.{$index}.source_url", $item["source_url"])'
            name="affected_legislation[{{ $index }}][source_url]"
            class="url-input"
            maxlength="1000"
          ></x-ui.input>

          @include('partials.notify.update-candidate.work-doc-selector-fields')

          <div class="flex justify-end mt-4 space-x-2">
            @if($canLink)
              @if($item->work_id && $item->catalogue_doc_id && !$item->work->uid)
                <x-ui.confirm-button
                    method="POST"
                    :route="route('collaborate.corpus.works.link-to-doc', ['work' => $item->work_id])"
                    :label="__('corpus.doc.link_doc_and_work')"
                    :confirmation="__('corpus.doc.link_doc_and_work_confirmation')"
                    styling="outline"
                    button-theme="dark"
                >
                  <x-slot:formBody>
                    <input type="hidden" name="catalogue_doc_id" value="{{ $item->catalogue_doc_id }}">
                    <input type="hidden" name="redirect_back" value="{{ url()->current() }}">
                  </x-slot:formBody>
                  {{ __('corpus.doc.link_doc_and_work') }}
                </x-ui.confirm-button>
              @endif

              @if($item->work_id && $item->catalogue_doc_id && $item->work->uid)
                <x-ui.confirm-button
                    method="DELETE"
                    :route="route('collaborate.corpus.works.unlink-from-doc', ['work' => $item->work_id])"
                    :label="__('corpus.doc.unlink_doc_and_work')"
                    :confirmation="__('corpus.doc.unlink_doc_and_work_confirmation')"
                    styling="outline"
                    button-theme="negative"
                >
                  <x-slot:formBody>
                    <input type="hidden" name="redirect_back" value="{{ url()->current() }}">
                  </x-slot:formBody>
                  {{ __('corpus.doc.unlink_doc_and_work') }}
                </x-ui.confirm-button>
              @endif
            @endif

            <x-ui.button styling="outline" theme="secondary" @click="deleted.push({{ $item['id'] }}); $event.target.closest('.group').remove()">
              {{ __('actions.delete') }}
            </x-ui.button>
          </div>
        </div>
      @endforeach

      <template x-for="num in legislation" :key="num">
        <div class="border-b border-libryo-gray-200 pb-4 group legislation-group">
          <input type="hidden" class="work-id" x-bind:name="'affected_legislation[' + num + '][work_id]'" value="">
          <input type="hidden" class="catalogue-doc-id" x-bind:name="'affected_legislation[' + num + '][catalogue_doc_id]'" value="">

          <x-ui.input
            :tomselect="false"
            required
            type="select"
            :options="$types"
            value=""
            :label="__('corpus.doc.legislation_type')"
            name="type"
            x-bind:name="'affected_legislation[' + num + '][type]'"
          ></x-ui.input>

          <div class="flex items-end group">
            <div class="flex-grow">
              <x-ui.input
                required
                value=""
                :label="__('interface.title')"
                name="title"
                x-bind:name="'affected_legislation[' + num + '][title]'"
                maxlength="1000"
                class="title-input"
              ></x-ui.input>
            </div>

            <div class="pb-1 ml-1">
              <x-ui.button
                styling="outline"
                class="tippy"
                data-tippy-content="{{ __('corpus.doc.paste_from_doc') }}"
                data-content="{{ $resource->title ?? '' }}"
                data-translation-content="{{ $resource->docMeta->title_translation ?? '' }}"
                @click="$el.closest('.group').querySelector('input').value = $el.getAttribute('data-content');$el.closest('.group').nextElementSibling.querySelector('input').value = $el.getAttribute('data-translation-content')"
              >
                <x-ui.icon name="paste" />
              </x-ui.button>
            </div>
          </div>

          <div>
            <x-ui.input
                value=""
                :label="__('corpus.work.title_translation')"
                name="title_translation"
                x-bind:name="'affected_legislation[' + num + '][title_translation]'"
                maxlength="1000"
                class="title-translation-input"
            ></x-ui.input>
          </div>

          <div class="flex items-end group">
            <div class="flex-grow">
              <x-ui.input
                  required
                  value=""
                  :label="__('corpus.work.source_url')"
                  name="source_url"
                  x-bind:name="'affected_legislation[' + num + '][source_url]'"
                  maxlength="1000"
                  class="url-input"
              ></x-ui.input>
            </div>

            <div class="pb-1 ml-1">
              <x-ui.button
                styling="outline"
                class="tippy"
                data-tippy-content="{{ __('corpus.doc.paste_from_doc') }}"
                data-content="{{ $resource->docMeta->source_url ?? '' }}"
                @click="$el.closest('.group').querySelector('input').value = $el.getAttribute('data-content')"
              >
                <x-ui.icon name="paste" />
              </x-ui.button>
            </div>
          </div>

          @include('partials.notify.update-candidate.work-doc-selector-fields', ['item' => new stdClass(), 'bound' => true])

          <div class="flex justify-end mt-4 space-x-2">
            <x-ui.button styling="outline" theme="secondary" @click="legislation.splice(legislation.indexOf(num), 1)">
              {{ __('actions.delete') }}
            </x-ui.button>
          </div>
        </div>
      </template>

      <div class="pt-4">
        <x-ui.button styling="outline" x-on:click="legislation.push(legislation.length + startCount);">
          <x-ui.icon name="plus"/>
          {{ __('actions.add') }}
        </x-ui.button>
      </div>
    </div>

  </div>

  <x-slot name="footer">
    <div></div>
    <div class="flex items-center justify-end" x-show="!selectingWork && !selectingDoc">
      <div>
        <x-ui.button type="button" theme="secondary" @click="open = false">{{ __('actions.close') }}</x-ui.button>
        <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
      </div>
    </div>
  </x-slot>
</x-ui.form>

