{{-- text-purple-600 bg-purple-100 text-white text-blue-600 bg-blue-100 text-green-600 bg-green-100 text-red-600 bg-red-100 text-libryo-gray-600 bg-libryo-gray-100 --}}
@php
  use App\Enums\Corpus\MetaChangeStatus;

  $isBeingRemoved = function ($key, $relation, $item) use ($reference, $metaDrafts) {
      foreach ($reference->{$metaDrafts[$key]} as $draft) {
          if ($draft->pivot->change_status !== MetaChangeStatus::REMOVE->value) {
              continue;
          }
          if ($draft->id === $item->id) {
              return true;
          }
      }
      return false;
  };
@endphp

@if ($meta->isNotEmpty())
  <div class="ml-4 space-y-2 mb-4 mt-2 text-xs" id="meta_{{ $reference->id }}" @click.stop="">
    @foreach ($meta as $key => $relation)
      <div class="flex items-start">
        <div class="flex-shrink-0 w-6 h-6 flex justify-start items-center">
          <x-ui.icon :name="$metaIcons[$key]" size="3" />
        </div>
        <div class="flex items-center shrink-0 mr-2">
          @if (Arr::get($permissions, "{$key}.attach", false))
            <a href="#" class="rounded-full border border-libryo-gray-200 px-1.5 pt-1 pb-0.5"
               @click.prevent.stop="toggleOpenRef($el);showMeta({{ $reference->id }}, '{{ $relation }}')">
              <x-ui.icon name="plus" size="3" />
            </a>
          @endif
        </div>
        <div class="flex flex-grow items-center flex-wrap">
          @foreach ($reference->{$relation} as $item)
            <span
                  class="mr-1 mb-1 px-3 pt-1 pb-0.5 rounded-full flex items-center {{ $metaClasses[$key] }} {{ $isBeingRemoved($key, $relation, $item) ? 'line-through' : '' }}"
                  style="{{ $key === 'topics' && $categoryTypes->has($item->category_type_id) ? sprintf('background-color:%s;', $categoryTypes->get($item->category_type_id)) : '' }}">
              <span class="mr-2">{{ $item->{$metaLabels[$key]} }}</span>
              @if (Arr::get($permissions, "{$key}.detach", false) && !$isBeingRemoved($key, $relation, $item))
                <x-ui.form method="DELETE" :action="route('collaborate.corpus.references.meta.destroy', [
                    ...request()->query(),
                    'expression' => $expression->id,
                    'reference' => $reference->id,
                    'relation' => $relation,
                    'related' => $item->id,
                ])">
                  <button type="submit">
                    <x-ui.icon name="times" size="3" />
                  </button>
                </x-ui.form>
              @endif
            </span>
          @endforeach

          @if ($reference->{$relation}->count() > 1 && Arr::get($permissions, "{$key}.detach", false))
            <div class="text-base font-normal">
              <x-ui.delete-button size="xs" :route="route('collaborate.corpus.references.meta.destroy', [
                  'expression' => $expression->id,
                  ...request()->query(),
                  'reference' => $reference->id,
                  'relation' => $relation,
                  'related' => 'all',
              ])">
                <x-ui.icon name="times" size="3"
                           data-tippy-content="{{ __('corpus.reference.delete_meta_tooltip') }}" class="tippy" />
              </x-ui.delete-button>
            </div>
          @endif
        </div>
      </div>

      @if ($reference->{$metaDrafts[$key]}->count() > 0)
        <div class="text-negative">
          * {{ __('corpus.reference.draft_changes') }}:
        </div>
        <div class="flex items-start">
          <div class="flex flex-grow items-center flex-wrap">
            @foreach ($reference->{$metaDrafts[$key]} as $item)
              <span
                    class="mr-1 mb-1 px-3 pt-1 pb-0.5 rounded-full flex items-center {{ $item->pivot->change_status === MetaChangeStatus::ADD->value ? $metaClasses[$key] : 'text-negative border border-negative bg-white' }}"
                    style="{{ $key === 'topics' && $categoryTypes->has($item->category_type_id) && $item->pivot->change_status === MetaChangeStatus::ADD->value ? sprintf('background-color:%s;', $categoryTypes->get($item->category_type_id)) : '' }}">
                <span
                      class="mr-2">
                  <span>{{ $item->pivot->change_status === MetaChangeStatus::ADD->value ? __('actions.add') : __('actions.remove') }}:</span>
                  <span data-annotation-source-id="{{ $item->pivot->annotation_source_id }}"
                        class="{{ $item->pivot->change_status === MetaChangeStatus::REMOVE->value ? ' line-through ' : '' }} {{ $item->pivot->annotation_source_id ? ' italic ' : '' }}">
                    {{ $item->{$metaLabels[$key]} }}
                  </span>
                </span>
                @if (Arr::get($permissions, "{$key}.detach", false))
                  <x-ui.form method="DELETE" :action="route('collaborate.corpus.references.meta-drafts.destroy', [
                      ...request()->query(),
                      'expression' => $expression->id,
                      'reference' => $reference->id,
                      'relation' => $relation,
                      'related' => $item->id,
                  ])">
                    <button type="submit" class="tippy" data-tippy-content="{{ __('interface.undo_changes') }}">
                      <x-ui.icon name="times" size="3" />
                    </button>
                  </x-ui.form>
                @endif
              </span>
            @endforeach
          </div>
        </div>
      @endif
    @endforeach
  </div>
@endif
