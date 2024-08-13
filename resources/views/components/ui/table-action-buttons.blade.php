@props(['baseRoute', 'basePermission', 'resourceId', 'withUpdate' => false, 'withDelete' => false, 'editLabel' => __('actions.edit'), 'deleteLabel' => __('actions.delete'), 'editPermissionSuffix' => 'update', 'deletePermissionSuffix' => 'delete'])

<div class="flex space-x-2 justify-end">
  @if ($withUpdate)
    @can("{$basePermission}.{$editPermissionSuffix}")
      <a class="text-primary font-semibold text-xs px-4 py-2 rounded-md hover:bg-primary hover:text-white"
         href="{{ route($baseRoute . '.edit', array_merge($baseRouteParams ?? [], (array) $resourceId)) }}">
        {{ $editLabel }}
      </a>
    @endcan
  @endif

  @if ($withDelete)
    @can("{$basePermission}.{$deletePermissionSuffix}")
      <x-ui.delete-button styling="flat" :route="route($baseRoute . '.destroy', array_merge($baseRouteParams ?? [], (array) $resourceId))">{{ $deleteLabel }}
      </x-ui.delete-button>
    @endcan
  @endif
</div>
