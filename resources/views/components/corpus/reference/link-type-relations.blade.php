@props(['reference', 'references', 'expression', 'task', 'type', 'previewing' => false])
@php
    $references = $references->groupBy('pivot.link_type');
    $data = $references->keys()->mapWithKeys(fn ($key) => ["{$type}_{$key}" => true]);
    $canDelete = !$previewing && auth()->user()->can('collaborate.corpus.reference.link.delete');
@endphp
<div x-data="{{ json_encode($data) }}" class="mt-4">
  @foreach($references as $key => $group)
    <div class="border-b border-norma-gray-200 pb-2">
      <div class="flex justify-between border-b pb-2">
        <div @click="{{ $type }}_{{ $key }} = !{{ $type }}_{{ $key }}" class="font-semibold cursor-pointer text-sm flex items-center">
          <x-ui.icon name="angle-down" size="3" x-show="{{ $type }}_{{ $key }}" />
          <x-ui.icon name="angle-right" size="3" x-show="!{{ $type }}_{{ $key }}" />
          <span>{{ __("corpus.reference.link_type_relations.{$key}_{$type}") }}</span>
        </div>

        <div class="px-2">
          @if($canDelete)
            @can("collaborate.corpus.reference.link.link_type_{$key}")
              <x-ui.delete-button
                route="{{ route('collaborate.work-expressions.references.'. $type .'.delete', ['expression' => $expression->id, 'related' => 'all', 'reference' => $reference->id]) }}"
                styling="outline"
                buttonTheme="tertiary"
                :confirmation="__('corpus.reference.unlink_all_confirmation')"
                data-tippy-content="{{ __('corpus.reference.unlink_all') }}" class="tippy"
              >
                <x-ui.icon name="unlink" size="4" />
              </x-ui.delete-button>
            @endcan
          @endif
        </div>
      </div>

      <x-corpus.reference.link-type-relations-list
          :reference="$reference"
          :group="$group"
          :key="$key"
          :type="$type"
          :expression="$expression"
          :can-delete="$canDelete"
      />
    </div>
  @endforeach
</div>
