<x-ui.tabs>
  <x-slot name="nav">
    <x-ui.tab-nav name="details">
      <x-ui.icon name="file-alt" class="mr-2"/>
      {{ __('requirements.requirement_details') }}
    </x-ui.tab-nav>

    <x-ui.tab-nav name="notes">
      <x-ui.icon name="sticky-note" class="mr-2"/>
      {{ __('requirements.summary.notes') }}
    </x-ui.tab-nav>

    <x-ui.tab-nav name="read-withs">
      <x-ui.icon name="object-union" class="mr-2"/>
      {{ __('requirements.read_withs') }}
    </x-ui.tab-nav>

  </x-slot>


  <x-ui.tab-content name="details">
    <div class="px-4 py-8">
      <livewire:corpus.reference-content-preview :wire:key="Str::random()" lazy :reference-id="$row->id" />
    </div>
  </x-ui.tab-content>

  <x-ui.tab-content name="notes">
    <div class="px-4 py-8">
      <livewire:requirements.summary-content-preview :wire:key="Str::random()" lazy :reference-id="$row->id" />
    </div>
  </x-ui.tab-content>

  <x-ui.tab-content name="read-withs">
    <div class="px-4 py-8">
      <livewire:corpus.reference-read-withs :wire:key="Str::random()" lazy :reference-id="$row->id" />
    </div>
  </x-ui.tab-content>
</x-ui.tabs>
