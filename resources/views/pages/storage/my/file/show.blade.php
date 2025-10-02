<x-layouts.app>
  <x-slot name="header">
    <div class="flex items-center">
      <div>
        <a href="{{ route('my.drives.folders.show', ['folder' => $file->folder_id]) }}" class="text-primary rounded-full border border-primary p-2 flex items-center justify-center">
          <x-ui.icon name="chevron-left" size="2" />
        </a>
      </div>

      <x-ui.file-icon :mime-type="$file->mime_type" class="mr-3 ml-5" size="8" />

      <div>
        <div>
          {{ $file->title }}
          @if ($file->norma)
            <span class="text-xs text-norma-gray-500 italic ml-3">{{ $file->norma->title }}</span>
          @endif
        </div>
        <div class="text-xs">
          {{ $file->description ?? '' }}
        </div>
      </div>
    </div>
  </x-slot>

  <x-slot name="actions">
    <div class="flex flex-row justify-between">
      <a target="_blank" href="{{ route('my.drives.files.download', ['file' => $file->id]) }}">
        <x-ui.button theme="primary">
          <x-ui.icon name="download" class="mr-2" size="3" />
          {{ __('actions.download') }}
        </x-ui.button>
      </a>

      @if (count($allowedActions) > 0)
        <x-ui.dropdown position="left">
          <x-slot name="trigger">
            <x-ui.button theme="primary" styling="outline" class="ml-2">
              {{ __('actions.more_actions') }}
              <x-ui.icon name="chevron-down" class="ml-2" size="3" />
            </x-ui.button>
          </x-slot>

          <x-storage.my.file-actions
                                     :file="$file"
                                     :allowed-actions="$allowedActions"
                                     :accepted-uploads="$acceptedUploads" />
        </x-ui.dropdown>
      @endif
    </div>
  </x-slot>

  @error('file')
    <div class="text-negative text-center pb-4">
      {{ $message }}
    </div>
  @enderror

  <div class="">
    <div class="col-span-2 bg-white shadow rounded-md">
      <x-ui.tabs :active="$canPreview ? 'preview' : 'info'">
        <x-slot name="nav">
          @if ($canPreview)
            <x-ui.tab-nav class="ml-3" name="preview">{{ __('storage.preview') }}</x-ui.tab-nav>
          @endif

          @can('my.storage.file.global.manage')
            @if ($file->isGlobal())
              <x-ui.tab-nav name="legal-domains">{{ __('nav.legal_domains') }}</x-ui.tab-nav>
              <x-ui.tab-nav name="locations">{{ __('nav.jurisdictions') }}</x-ui.tab-nav>
            @endif
          @endcan
        </x-slot>

        <div class="mt-5 px-5">

          @if ($canPreview)
            <x-ui.tab-content name="preview">
              <iframe src="{{ route('my.drives.files.stream', ['file' => $file->id]) }}" class="w-full"
                      height="1000"></iframe>
            </x-ui.tab-content>
          @endif

          @can('my.storage.file.global.manage')
            @if ($file->isGlobal())
              <x-ui.tab-content name="legal-domains">
                <turbo-frame
                             loading="lazy"
                             src="{{ route('my.legal-domains.for.file.index', ['file' => $file->id]) }}"
                             id="legal-domain-for-file-{{ $file->id }}">
                  <x-ui.skeleton />
                </turbo-frame>
              </x-ui.tab-content>

              <x-ui.tab-content name="locations">
                <turbo-frame
                             loading="lazy"
                             src="{{ route('my.locations.for.file.index', ['file' => $file->id]) }}"
                             id="location-for-file-{{ $file->id }}">
                  <x-ui.skeleton />
                </turbo-frame>
              </x-ui.tab-content>
            @endif
          @endcan
        </div>

      </x-ui.tabs>
    </div>
  </div>

  {{-- LEFT SIDE --}}
  <div class="grid lg:grid-cols-12 lg:gap-10">
    <div class="lg:col-span-7">
      <x-ui.tabs>
        <x-slot name="nav">
          <x-ui.tab-nav name="info">{{ __('storage.file_information') }}</x-ui.tab-nav>
        </x-slot>

        <x-ui.tab-content name="info">
          <dl class="mt-2 divide-y divide-norma-gray-200">
            <div class="py-3 flex justify-between text-sm font-medium">
              <dt class="text-norma-gray-500">{{ __('storage.drives.file_type') }}</dt>
              <dd class="text-norma-gray-900">{{ $file->mime_type }}</dd>
            </div>

            <div class="py-3 flex justify-between text-sm font-medium">
              <dt class="text-norma-gray-500">{{ __('storage.drives.size') }}</dt>
              <dd class="text-norma-gray-900">{{ $file->getHumanReadableSize() }}</dd>
            </div>

            <div class="py-3 flex justify-between text-sm font-medium">
              <dt class="text-norma-gray-500">{{ __('storage.drives.uploaded_by') }}</dt>
              <dd class="text-norma-gray-900">{{ $file->author?->fullName ?? '' }}</dd>
            </div>

            <div class="py-3 flex justify-between text-sm font-medium">
              <dt class="text-norma-gray-500">{{ __('timestamps.created_at') }}</dt>
              <dd class="text-norma-gray-900">
                <x-ui.timestamp :timestamp="$file->created_at" />
              </dd>
            </div>
            @if ($file->expires_at)
              <div class="py-3 flex justify-between text-sm font-medium">
                <dt class="text-norma-gray-500">{{ __('timestamps.expires_at') }}</dt>
                <dd class="text-norma-gray-900">
                  <x-ui.timestamp :timestamp="$file->expires_at" />
                </dd>
              </div>
            @endif
          </dl>
          {{-- user tags --}}
          <div class="py-5">
            @foreach ($file->userTags as $tag)
              <x-ui.badge>{{ $tag->title }}</x-ui.badge>
            @endforeach
          </div>
        </x-ui.tab-content>
      </x-ui.tabs>

    </div>

    {{-- RIGHT SIDE --}}
    <div class="lg:col-span-5">

      <x-ui.tabs>

        <x-slot name="nav">
          @ifOrgHasModule('tasks')
          <x-ui.tab-nav name="tasks">{{ __('nav.tasks') }}</x-ui.tab-nav>
          @endifOrgHasModule

          @ifOrgHasModule('comments')
          <x-ui.tab-nav name="comments">{{ __('notify.legal_update.comments') }}</x-ui.tab-nav>
          @endifOrgHasModule

          {{-- //can be removed if not reverted before monolith launch --}}
          {{-- <x-ui.tab-nav name="reminders">{{ __('nav.reminders') }}</x-ui.tab-nav> --}}

        </x-slot>

        <div class="pt-5">
          @ifOrgHasModule('tasks')
          <x-ui.tab-content name="tasks">
            <turbo-frame loading="lazy"
                         src="{{ route('my.tasks.tasks.for.related.index', ['relation' => 'file', 'id' => $file->id]) }}"
                         id="tasks-for-file-{{ $file->id }}">
              <x-ui.skeleton />
            </turbo-frame>
          </x-ui.tab-content>
          @endifOrgHasModule

          @ifOrgHasModule('comments')
          <x-ui.tab-content name="comments">

            <turbo-frame src="{{ route('my.comments.for.commentable', ['type' => 'file', 'id' => $file->id]) }}"
                         loading="lazy" id="comments-for-file-{{ $file->id }}">
              <x-ui.skeleton />
            </turbo-frame>
          </x-ui.tab-content>
          @endifOrgHasModule

          {{-- //can be removed if not reverted before monolith launch --}}
          {{-- <x-ui.tab-content name="reminders">
            <turbo-frame loading="lazy"
                         src="{{ route('my.notify.reminders.for.related.index', ['relation' => 'file', 'id' => $file->id]) }}"
                         id="reminders-for-file-{{ $file->id }}">
              <x-ui.skeleton />
            </turbo-frame>
          </x-ui.tab-content> --}}
        </div>

      </x-ui.tabs>

    </div>
  </div>


</x-layouts.app>
