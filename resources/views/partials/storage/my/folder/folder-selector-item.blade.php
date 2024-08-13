<turbo-frame id="folder-tree-item-{{ $folder->id ?? 'root' }}-{{ $uniqueId }}">
  <li class="relative bg-white list-none">
    @if (!empty($folder))
      <div class="flex justify-between space-x-1">

        <div class="w-7">
          @if ($folder->children_count > 0)
            @if (!$showingChildren)
              <a
                 href="{{ route('my.drives.folders.tree', ['folder' => $folder->id, 'show' => 1, 'uniqueId' => $uniqueId]) }}">
                <x-ui.icon name="plus-square" />
              </a>
            @else
              <a
                 href="{{ route('my.drives.folders.tree', ['folder' => $folder->id, 'show' => 0, 'uniqueId' => $uniqueId]) }}">
                <x-ui.icon name="minus-square" />
              </a>
            @endif
          @endif
        </div>

        <div class="min-w-0 flex-1">
          <div @click="selectFolder({{ $folder->id }}, '{{ $folder->title }}');"
               class="flex flex-row cursor-pointer hover:bg-libryo-gray-100 px-3 py-1"
               :class="selected === {{ $folder->id }} ? 'bg-primary-lighter text-white hover:bg-primary-lighter hover:text-white' : ''">
            <x-ui.icon name="{{ $showingChildren ? 'folder-open' : 'folder' }}" class="mr-3" />
            <div>{{ $folder->title }}</div>
          </div>
        </div>
      </div>
    @endif

    @if (!empty($children) && $showingChildren)
      <ul role="list" class="pl-5">
        @foreach ($children as $child)
          @include('partials.storage.my.folder.folder-selector-item', [
          'folder' => $child,
          'uniqueId' => $uniqueId,
          'showingChildren' => false,
          'children' => []
          ])
        @endforeach
      </ul>
    @endif
  </li>
</turbo-frame>
