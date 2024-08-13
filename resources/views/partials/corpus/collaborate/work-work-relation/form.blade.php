<x-ui.alert-box type="info" class="mb-2 flex items-center">
  <span>{{ __("corpus.work.attch_{$relation}_works_description", ['work' => $selected->title_translation ?? $selected->title]) }}</span>
</x-ui.alert-box>

<div x-data="{ works: [], selecting: true }">
  <div class="-ml-2 grid grid-cols-3 gap-4" @@selected="if(!works.find(function (w) { return w.id == $event.detail.id })) { works.push($event.detail); }selectedWork = {};">
    <div x-show="selecting" class="col-span-2 no-shadow">
      <turbo-frame id="work-selector-works-list" src="{{ route('collaborate.works.for.selector.index') }}" loading="lazy">
        <x-ui.skeleton />
      </turbo-frame>
    </div>

    <div class="mt-6 text-sm">
      <div class="font-semibold px-2 mb-2 pb-2 border-b border-libryo-gray-100">
        {{ __('corpus.work.selected_works') }}
      </div>

      @error('works')
      <x-ui.alert-box type="negative" class="mb-2 flex items-center">
        <span>{{ __('corpus.work.works_required') }}</span>
      </x-ui.alert-box>
      @enderror

      <table class="w-full">
        <template x-for="(work,index) in works">
          <tr class="group" x-bind:class="index % 2 == 0 ? 'bg-white' : 'bg-libryo-gray-50'" @click="works.splice(index, 1)">
            <td class="px-2 py-1 whitespace-normal text-sm font-medium text-libryo-gray-900 relative">
              <span x-text="work.title"></span>
              <span class="cursor-pointer hidden group-hover:flex bg-gray-50 bg-opacity-90 absolute top-0 left-0 w-full h-full items-center justify-center">
                <span>{{ __('actions.click_to_remove') }}</span>
              </span>
            </td>
          </tr>
        </template>
      </table>
    </div>
  </div>

  <template x-for="work in works">
    <input type="hidden" name="works[]" x-bind:value="work.id">
  </template>
</div>


<x-slot name="footer">
  <div></div>
  <x-ui.button theme="primary" type="submit">{{ __('actions.attach') }}</x-ui.button>
</x-slot>


