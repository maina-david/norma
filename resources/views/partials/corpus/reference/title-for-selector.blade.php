@php
  $label = trim(preg_replace('/(\n|\s)+/', ' ', $reference->refPlainText?->plain_text ?? ''));
  $contentPrefix = \App\Enums\Application\ApplicationType::my()->is(\App\Managers\AppManager::getApp()) ? 'my.settings' : 'collaborate';
@endphp
<div x-data="{ collapsed: true }">
  <div class="flex justify-between">
    <div
         @click="$dispatch('selected', { id: {{ $reference->id }}, label: '{{ str_replace('\'', '\\\'', $label) }}' }); open = false;"
         class="text-primary cursor-pointer flex items-center w-screen-50 selector-option">
      <span class="w-8">
        <x-ui.icon x-show="typeof selectedReferences !== 'undefined' && selectedReferences.includes({{ $reference->id }}) "
                   name="check" />
      </span>
      <span>{{ $label }}</span>
    </div>


    <button type="button" @click="collapsed = !collapsed">
      <x-ui.icon name="eye" />
    </button>
  </div>

  <div class="py-2 w-screen-50 ml-8" x-show="!collapsed">
    <x-turbo::frame
                   id="selector_reference_{{ $reference->id }}"
                   loading="lazy"
                   :src="route($contentPrefix . '.corpus.reference.for.selector.content', [
                       'reference' => $reference->id,
                   ])">
      <x-ui.skeleton />
    </x-turbo::frame>
  </div>
</div>
