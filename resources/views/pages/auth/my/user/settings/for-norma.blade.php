<x-customer.norma.my.settings.layout :norma="$norma">

  <div class="my-5">
    <x-ui.alert-box type="info" class="mb-2">
      {{ __('customer.norma.users_have_access_info', ['organisation' => $organisation ? $organisation->title : __('customer.organisation.all_organisations')]) }}
    </x-ui.alert-box>
  </div>

  <x-auth.user.user-data-table :base-query="$baseQuery"
                               :route="route('my.settings.users.for.norma.index', ['norma' => $norma->id])"
                               :fields="['avatar', 'name', 'email', 'active']"
                               searchable
                               :paginate="50"
                               :fields="['avatar', 'name', 'email', 'active']">
    <x-slot name=" actionButton">

    </x-slot>
  </x-auth.user.user-data-table>
</x-customer.norma.my.settings.layout>
