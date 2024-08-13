<div class="flex-grow overflow-y-auto custom-scroll">
  <x-workflows.comment-box name="note" contentField="content" route-prefix="collaborate.notes" :route-params="$routeParams" :resource="$resource ?? null" />

  <div class="space-y-4 pb-10">
    @foreach($notes as $note)
      <div class="border border-libryo-gray-200 rounded-xl">
        <div class="flex items-start">
          <div class="py-4 pl-4 flex-grow">
            <x-ui.collaborate.wysiwyg-content :content="$note->content" />
          </div>

          @canany(['update', 'delete'], $note)
            <x-ui.dropdown position="left">
              <x-slot name="trigger">
                <button class="mt-2 mr-2" type="button">
                  <x-ui.icon name="ellipsis-vertical" />
                </button>
              </x-slot>

              <div class="divide-y divide-libryo-gray-200 text-sm">
                @can('update', $note)
                  <a class="px-4 py-1 flex items-center" href="{{ route('collaborate.notes.edit', ['note' => $note->id]) }}">
                    <x-ui.icon name="pencil" size="4" />
                    <span class="ml-2">Edit</span>
                  </a>
                @endcan

                @can('delete', $note)
                  <x-ui.delete-button class="rounded-l-none rounded-r-none" button-theme="negative" styling="flat" :route="route('collaborate.notes.destroy', ['note' => $note->id])">
                    <x-ui.icon name="trash" size="4" />
                    <span class="ml-2">Delete</span>
                  </x-ui.delete-button>
                @endcan
              </div>
            </x-ui.dropdown>
          @endcanany
        </div>

        <div class="flex justify-end items-center p-1 space-x-2">
          <span class="text-xs text-libryo-gray-600">{{ $note->created_at->diffForHumans() }}</span>
          @if($note->collaborator->full_name ?? false)
            <x-ui.badge small>
              <span class="px-2">{{ $note->collaborator->full_name }}</span>
            </x-ui.badge>
          @endif
        </div>

      </div>
    @endforeach
  </div>

</div>

<div class="flex-shrink-0 px-2 pt-1 no-pagination-text pagination-tight">
  {{ $notes->withQueryString()->links() }}
</div>
