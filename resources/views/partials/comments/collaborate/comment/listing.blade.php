<div>
  <x-workflows.comment-box name="comment" contentField="comment" route-prefix="collaborate.comments" :route-params="$routeParams" :resource="$resource ?? null" />

  <div class="space-y-4 h-full overflow-y-auto custom-scroll pb-10" style="max-height:60vh;">
    @foreach($comments as $comment)
      <div class="border rounded-xl" style="border-color:{{ $comment->task->taskType->colour }};">
        <div class="flex items-start">
          <div class="py-4 pl-4 flex-grow">
            <div class="flex space-x-2">
              <div class="w-8">
                @if($comment->author)
                  <x-ui.user-avatar dimensions="6" :user="$comment->author" />
                @endif
              </div>
              <x-ui.collaborate.wysiwyg-content :content="$comment->comment" />
            </div>
          </div>

          @canany(['update', 'delete'], $comment)
          <x-ui.dropdown position="left">
            <x-slot name="trigger">
              <button class="mt-2 mr-2" type="button">
                <x-ui.icon name="ellipsis-vertical" />
              </button>
            </x-slot>

            <div class="divide-y divide-libryo-gray-200 text-sm">
              @can('update', $comment)
              <a class="px-4 py-1 flex items-center" href="{{ route('collaborate.comments.edit', ['comment' => $comment->id]) }}">
                <x-ui.icon name="pencil" size="4" />
                <span class="ml-2">Edit</span>
              </a>
              @endcan

              @can('delete', $comment)
              <x-ui.delete-button class="rounded-l-none rounded-r-none" button-theme="negative" styling="flat" :route="route('collaborate.comments.destroy', $comment->id)">
                <x-ui.icon name="trash" size="4" />
                <span class="ml-2">Delete</span>
              </x-ui.delete-button>
              @endcan
            </div>
          </x-ui.dropdown>
          @endcanany
        </div>

        <div class="flex justify-end items-center p-1 space-x-2">
          <span class="text-xs text-libryo-gray-600">{{ $comment->created_at->diffForHumans() }}</span>
          <x-workflows.task-type.task-type-badge :type="$comment->task->taskType" />
        </div>

      </div>
    @endforeach
  </div>

</div>
