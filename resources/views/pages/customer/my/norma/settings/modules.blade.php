<x-customer.norma.my.settings.layout :norma="$norma">
  <x-ui.form method="post"
             :action="route('my.settings.normas.modules.update', ['norma' => $norma->id])">
    <div class="my-8">
      @foreach ($modules as $module => $value)
        <div class="mt-3">
          <x-ui.input type="checkbox"
                      :value="$value"
                      :name="$module"
                      label="{{ __('customer.norma.modules.' . $module) }}" />
        </div>
      @endforeach
    </div>

    <x-slot name="footer">
      <div>
        <x-ui.button type="submit" theme="primary">{{ __('actions.update') }}</x-ui.button>
      </div>
    </x-slot>
  </x-ui.form>
</x-customer.norma.my.settings.layout>
