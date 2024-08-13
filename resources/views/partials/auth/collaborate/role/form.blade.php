<x-ui.input :value="old('title', $resource->title ?? '')" name="title" label="{{ __('auth.role.title') }}" required />

@error('permissions')
  <div class="text-sm text-red-400">{{ $message }}</div>
@enderror
@php($oldPermissions = old('permissions') ? array_keys(old('permissions')) : $resource->permissions ?? [])

@foreach ($permissions as $key => $group)
  <div class="mt-4 font-semibold">{{ $key }}</div>

  <div class="flex flex-wrap mt-2">
    @foreach ($group as $item)
      <div class="w-1/2 md:w-1/3 mt-2">
        <x-ui.input type="checkbox"
                    :value="in_array($item->value, $oldPermissions)"
                    :label="__($item->label)"
                    :name="$item->form" />
      </div>
    @endforeach
  </div>
@endforeach


<x-slot name="footer">
  <div></div>
  <div>
    <x-ui.back-button :fallback="route('collaborate.roles.index')" />
    <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
  </div>
</x-slot>
