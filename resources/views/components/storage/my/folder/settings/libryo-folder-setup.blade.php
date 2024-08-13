<div>
  <ul role="list" class="divide-y divide-libryo-gray-200">
    @foreach ($folders as $folder)

      <li class="py-1">
        <div
             class="flex items-center space-x-4 {{ $folder->isHiddenInSettings($folderSettings) ? 'text-libryo-gray-400' : 'text-libryo-gray-800' }}">
          <div class="flex-shrink-0">
            <x-ui.icon name="folder" />
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium truncate">
              {{ $folder->title }}
            </p>
          </div>
          <div>
            @if ($folder->isHiddenInSettings($folderSettings))
              <x-ui.button theme="primary" type="link"
                           :href="route('my.settings.drives.setup.hide-or-show.folder', ['folder' => $folder, 'hideOrShow' => 'show'])">
                {{ __('actions.show') }}</x-ui.button>
            @else
              <x-ui.button styling="outline" theme="gray" type="link"
                           :href="route('my.settings.drives.setup.hide-or-show.folder', ['folder' => $folder, 'hideOrShow' => 'hide'])">
                {{ __('actions.hide') }}</x-ui.button>
            @endif

          </div>
        </div>
      </li>
    @endforeach
  </ul>
</div>
