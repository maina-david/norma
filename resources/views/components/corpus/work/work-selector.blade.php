@php
$filteredWork = $work ? json_encode(['id' => $work->id, 'title' => $work->title]) : '{}';
@endphp

<div
  class="work-selector"
  x-data="{selectedWork: {{ $filteredWork }}, originalWork: {{ $filteredWork }}}"
  x-init="$watch('selectedWork', value => $dispatch('selected-work', value))"
  {{ $attributes }}
>

  <x-ui.dropdown teleport>
    <x-slot name="trigger">
      <x-ui.button theme="primary" styling="outline" class="ml-2">

        <span x-show="selectedWork.id" x-text="selectedWork.title"></span>
        <span x-show="!selectedWork.id">{{ __('corpus.work.select_work') }}</span>
        <x-ui.icon name="chevron-down" class="ml-2" size="3" />
      </x-ui.button>
    </x-slot>


    <div class="w-screen h-screen overflow-y-auto fixed inset-0" @@selected="selectedWork = $event.detail; $dispatch('work-selected', $event.detail);">
      <button class="absolute right-4 top-4" @click="open = false">
        <x-ui.icon name="times-circle" size="6" />
      </button>
      <turbo-frame id="{{ $frame ?? 'work-selector-works-list' }}" src="{{ $listRoute }}" loading="lazy">
        <x-ui.skeleton />
      </turbo-frame>
    </div>
  </x-ui.dropdown>

  <input type="hidden" name="{{ $name }}" x-model="selectedWork.id" />
</div>
