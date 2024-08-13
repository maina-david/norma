@props(['workId', 'expressionId', 'referenceId', 'taskId', 'hasConsequences', 'hasRequirement'])
@php
  $options = \App\Enums\Corpus\ReferenceLinkType::linkTypes($hasConsequences);
  $options[''] = '';
  $routeParams = json_encode([
    \App\Enums\Corpus\ReferenceLinkType::AMENDMENT->value => $hasRequirement ? '' : '&has-requirement=3',
    \App\Enums\Corpus\ReferenceLinkType::CONSEQUENCE->value => $hasRequirement ? '' : '&has-requirement=3',
    \App\Enums\Corpus\ReferenceLinkType::READ_WITH->value => $hasRequirement ? '' : '&has-requirement=3',
  ]);

  $routeParams = str_replace('"', "'", $routeParams);
@endphp

@if(count($options) > 1)

<div
  x-data="{
    label: '',
    linkType: null,
    fetchFilter:{query: ''},
    options: {{ $routeParams }},
    setRelation: function (setTo, setLabel) {
      var selected = (setTo.match(/\d/) || [])[0];
      var selector = this.$refs.refSelector.querySelector('#multi-reference-selector-references-for-work');
      this.fetchFilter.query = '?select-all=1' + this.options[selected];
      selector.src = selector.src.split('?')[0] + this.fetchFilter.query;
      this.label = setLabel;
      this.linkType = setTo;
    },
  }"
>


  <x-ui.modal
    @closed="function () {
      var ws = $el.querySelector('.work-selector');
      ws._x_dataStack[0].selectedWork = Object.assign({}, ws._x_dataStack[0].originalWork || {});
      var rs = $el.querySelector('#multi-reference-selector-references-for-work');
      rs.src = rs.src.replace(/\/([0-9]+)$/, '/' + ws._x_dataStack[0].selectedWork.id)
    }"
  >
    <x-slot name="trigger">

      <x-ui.dropdown open-variable="openDrop">
        <x-slot name="trigger">
          <x-ui.button styling="outline">
            <span class="mr-2">{{ __("corpus.reference.add_relationship") }}</span>
          </x-ui.button>
        </x-slot>

        <div class="text-left flex flex-col">
          @foreach($options as $val => $label)
            <button
              class="text-left text-sm px-4 py-2 whitespace-nowrap hover:text-primary"
              @click="function () { this.setRelation('{{ $val }}', '{{ $label }}');this.openDrop = false;this.open = true; }"
            >
              {{ $label }}
            </button>
          @endforeach
        </div>
      </x-ui.dropdown>

    </x-slot>

    <div class="w-screen-75">
      <x-ui.form method="POST" :action="route('collaborate.work-expressions.references.relations.store', ['expression' => $expressionId, 'reference' => $referenceId])">
        <div class="font-semibold px-2" x-html="label"></div>

        <input x-bind:value="linkType" name="link_type" type="hidden" />

        <div class="mt-4" x-ref="refSelector">
          <x-corpus.reference.multi-reference-selector
            name="references[]"
            :work-id="$workId"
            :app-type="\App\Enums\Application\ApplicationType::collaborate()"
          />
        </div>

        <div class="mt-4 flex justify-end">
          <x-ui.button type="submit" theme="primary">
            {{ __('corpus.reference.link.link') }}
          </x-ui.button>
        </div>

      </x-ui.form>
    </div>
  </x-ui.modal>

</div>

@endif
