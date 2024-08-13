@php
  $user = auth()->user();
@endphp

<x-layouts.collaborate fluid no-padding>
  <x-slot name="pageHead">
    @vite(['resources/js/collaborate/toc.js'])
  </x-slot>

  <x-slot name="header">
    <x-turbo::frame target="_top" id="workflow-task-header" :src="route('collaborate.workflow.tasks.header', ['expression' => $expression->id])" />
  </x-slot>

  <div
    class="h-full"
    id="app"
    data-apply-pending="{{ $user->can('collaborate.corpus.reference.toc.apply-all-pending') ? 1 : 0 }}"
    data-generate="{{ $user->can('collaborate.corpus.reference.toc.generate-citations') ? 1 : 0 }}"
    data-delete="{{ $user->can('collaborate.corpus.reference.toc.delete-citations') ? 1 : 0 }}"
    data-expression="{{ $expression->id }}"
    data-last-volume="{{ $expression->volume }}"
  ></div>
</x-layouts.collaborate>
