@php
use Illuminate\Support\Str;
@endphp

@props(['name' => 'folder', 'uniqueId' => Str::random()])
<div {{ $attributes->merge(['class' => '']) }}
     x-data="{ selected: null, selectedTitle: null, selectFolder: function(id, title) { this.selected = id; this.selectedTitle = title; $dispatch('selected', id) } }">
  <input x-model="selected" type="hidden" name="{{ $name }}" />
  <x-ui.dropdown class="w-96">
    <x-slot name="trigger">
      <div class="p-3 border rounded-md border-libryo-gray-200 flex flex-row justify-between bg-white">
        <div class="text-ellipsis" x-text="selectedTitle"></div>
        <div>
          <x-ui.icon name="chevron-down" size="3" class="mx-2" />
        </div>
      </div>
    </x-slot>
    <div class="w-96">

      <turbo-frame loading="lazy" id="folder-tree-item-root-{{ $uniqueId }}"
                   src="{{ route('my.drives.folders.tree', ['show' => 1, 'uniqueId' => $uniqueId]) }}">
        <x-ui.skeleton />
      </turbo-frame>
    </div>
  </x-ui.dropdown>

</div>
