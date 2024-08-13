<x-customer.libryo.my.settings.layout :libryo="$libryo">
  <x-ui.form method="post"
             :action="route('my.settings.libryos.modules.update', ['libryo' => $libryo->id])">
    <div class="my-8">
      @foreach ($modules as $module => $value)
        <div class="mt-3">
          <x-ui.input type="checkbox"
                      :value="$value"
                      :name="$module"
                      label="{{ __('customer.libryo.modules.' . $module) }}" />
        </div>
      @endforeach
    </div>

    <x-slot name="footer">
      <div>
        <x-ui.button type="submit" theme="primary">{{ __('actions.update') }}</x-ui.button>
      </div>
    </x-slot>
  </x-ui.form>
</x-customer.libryo.my.settings.layout>
