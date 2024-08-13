<x-ui.show-field :label="__('auth.role.title')" :value="$resource->title" />

@php($oldPermissions = old('permissions') ? array_keys(old('permissions')) : $resource->permissions)

<div class="text-sm mt-4 mb-2 text-libryo-gray-500">
  {{ __('auth.role.permissions') }}
</div>

@foreach ($permissions as $key => $group)
  <div class="font-semibold mt-4">{{ $key }}</div>

  <div class="flex flex-wrap">
    @foreach ($group as $item)
      <div class="w-1/2 md:w-1/3 mt-1">
        <x-ui.show-field type="checkbox" :label="__($item->label)" :value="in_array($item->value, $oldPermissions)" />
      </div>
    @endforeach
  </div>
@endforeach
