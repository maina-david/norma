@php
$selectionAction = function ($user) use ($multiple) {
  $action = "selected.includes('{$user->hash_id}') ? selected.splice(selected.indexOf('{$user->hash_id}'), 1) : selected.push('{$user->hash_id}')";
  return $multiple ? $action : '';
}
@endphp
<div>
  <div class="bg-white flex flex-col px-1 pt-1 pb-4 h-full max-h-[20rem]" x-data="{ search: '', selected: {!! str_replace('"', "'", json_encode($selected)) !!} }">
    <div class="flex-shrink-0 px-2 flex items-center relative w-full mb-2">
      <div class="absolute left-0 top-0 bottom-0 flex items-center pl-6 pt-1">
        <x-ui.icon name="search" size="4" />
      </div>
      <div class="flex-grow">
        <x-ui.input class="pl-10" name="search" :placeholder="__('interface.search')" x-model="search" />
      </div>
    </div>

    <div class="flex-grow overflow-y-auto custom-scroll">
      @foreach($users as $user)
        <div
          x-show="search.length < 2 || `{{ strtolower(str_replace(' ',  '', $user->full_name)) }}`.includes(search)"
          class="flex items-center hover:bg-libryo-gray-100 px-3 py-1 rounded-lg cursor-pointer"
          @click.stop="$dispatch('selected', '{{ $user->hash_id }}');{{ $selectionAction($user) }}"
        >
          <x-ui.user-avatar class="flex-shrink-0" size="4" :user="$user" />
          <div class="ml-2 flex-grow">{{ $user->full_name }}</div>
          <div class="w-2 flex-shrink-0">
            <x-ui.icon x-show="selected.includes('{{ $user->hash_id }}')" name="check" size="4" class="text-primary" />
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>
