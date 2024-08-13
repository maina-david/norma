@php
  use App\Models\Corpus\Reference;
  use App\Services\Customer\ActiveLibryosManager;
@endphp
<div class="px-2">
  <div class="text-primary flex justify-between items-center border-b border-libryo-gray-200 py-6 px-4">
    <div class="flex items-center">
      <x-ui.icon name="radar"/>
      <div class="ml-2">{{ __('tasks.suggested_tasks') }}</div>
    </div>

    <livewire:tasks.create-requirement-task-button
      wire:key="{{ Str::random() }}"
      :reference-id="$row->id"
      :libryo-id="app(ActiveLibryosManager::class)->getActive()?->id"
      :area-id="$actionAreaId"
    />
  </div>
</div>

<div>
  <livewire:corpus.reference-content-extracts-preview
    :area-id="$actionAreaId"
    wire:key="{{ Str::random() }}"
    :reference-id="$row->id"
  />
</div>
