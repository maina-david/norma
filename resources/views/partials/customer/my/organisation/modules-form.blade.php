@php
  use App\Enums\System\OrganisationModules;
  $normaModules = OrganisationModules::forSelector();
@endphp
<x-ui.form
  method="post"
  :action="route('my.settings.organisations.modules.update', ['organisation' => $organisation->id])"
>
  <div class="my-8">
    @foreach ($normaModules as $item)
      <div class="mt-3">
        <x-ui.input
          type="checkbox"
          :value="$modules[$item['value']] ?? false"
          :name="$item['value']"
          :label="$item['label']"
        />
      </div>
    @endforeach
  </div>

  <x-slot name="footer">
    <div>
      <x-ui.button type="submit" theme="primary">{{ __('actions.update') }}</x-ui.button>
    </div>
  </x-slot>
</x-ui.form>
