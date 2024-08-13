{{-- A plus icon button with a modal that shows and a remote select of items to add to an item --}}
@props(['itemsName', 'actionRoute', 'route', 'placeholder', 'tooltip'])

<x-ui.modal>
  <x-slot name="trigger">
    <x-ui.button type="button" @click="open = true" styling="flat" theme="tertiary" class="mt-1 tippy"
                 data-tippy-content="{{ $tooltip }}" data-tippy-delay="450">
      <x-ui.icon name="plus" class="text-base" />
    </x-ui.button>
  </x-slot>

  <div class="flex justify-end items-center p-3 rounded-md bg-libryo-gray-50">
    <x-ui.form :action="$actionRoute" method="post">
      <div class="w-96">
        <x-ui.input name="{{ $itemsName }}[]"
                    type="select"
                    class="remote-select"
                    multiple
                    placeholder="{{ $placeholder }}"
                    data-load="{{ $route }}"
                    data-load-value-field="{{ $loadValueField ?? 'id' }}"
                    data-load-label-field="{{ $loadLabelField ?? 'title' }}"
                    data-load-search-field="{{ $loadSearchField ?? 'title' }}"
                    data-no-results="{{ __('actions.errors.no_results_found_for') }}"
                    data-keep-typing="{{ __('actions.errors.keep_typing') }}" />

      </div>

      <div class="flex justify-end w-full mt-3">
        <x-ui.button type="submit" theme="primary">{{ __('actions.add') }}</x-ui.button>
      </div>

    </x-ui.form>
  </div>
</x-ui.modal>
