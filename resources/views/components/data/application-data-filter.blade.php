<div class="px-4 py-2 bg-white lg:shadow-sm rounded-md pb-6">
  <div x-data="{ open: false }" class="lg:block w-96 sm:w-60 max-w-screen-75" :class="{ hidden: !open, block: open }">
    <form method="GET">

      <x-ui.params-to-form :exclude="collect($filters)->map->name->all()" />

      <div class="flex justify-end mt-4">
        <div class="items-center space-x-4">
          <x-ui.button type="link" href="{{ request()->url() }}" theme="primary" styling="outline">
            {{ __('actions.clear') }}
          </x-ui.button>

          <x-ui.button theme="primary" type="submit" @click="$dispatch('filtered')">
            {{ __('interface.apply') }}
          </x-ui.button>
        </div>
      </div>

      @foreach($filters as $index => $filter)
        @if(!$filter->hidden)
          @if($filter->viewFile)
            @include($filter->viewFile, ['value' => $filter->value, 'label' => $filter->label])
          @else
            <div class="{{ $filter->type === 'checkbox' ? 'mt-4' : '' }}">
              <x-ui.input
                  name="{{ $filter->name }}{{ $filter->multiple ? '[]' : '' }}"
                  :type="$filter->type"
                  :options="['' => '-'] + $filter->options"
                  :multiple="$filter->multiple"
                  :label="$filter->label"
                  :value="$filter->value"
              />
            </div>
          @endif
        @endif
      @endforeach

      <div class="flex justify-end mt-4">
          <div class="items-center space-x-4">
            <x-ui.button type="link" href="{{ request()->url() }}" theme="primary" styling="outline">
              {{ __('actions.clear') }}
            </x-ui.button>

            <x-ui.button theme="primary" type="submit" @click="$dispatch('filtered')">
                {{ __('interface.apply') }}
            </x-ui.button>
          </div>
      </div>
    </form>
  </div>
</div>

