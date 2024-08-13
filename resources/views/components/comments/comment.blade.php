@props(['comment', 'user', 'reply' => false, 'redirect' => null])
<div x-data="{
  showingReplies: false,
  showingFiles: false,
  hoverTimeout: null
}">
  <div class="hover:bg-libryo-gray-50 py-2">

    <div class="flex flex-row justify-between mb-2">
      <div class="mr-3 flex-shrink-0">
        <x-ui.user-avatar :user="$comment->author" :dimensions="12" />
      </div>
      <div class="w-full">
        <div class="flex flex-fow justify-between">
          <div class=" col-span-2 text-sm font-medium mb-2">
            {{ $comment->author->full_name }}
          </div>
          <div class="col-span-1 flex flex-row ">
            @if (!$reply)
              <div @click="showingReplies = true" class="mr-5">
                <x-ui.icon name="reply" class="cursor-pointer tippy"
                           data-tippy-content="{{ __('comments.reply_to_comment') }}" />
              </div>
            @endif
            @if ($user->can('attachFiles', $comment))
              <div @click="showingFiles = true" class="mr-5">
                <x-ui.icon name="paperclip" class="cursor-pointer tippy"
                           data-tippy-content="{{ __('storage.attach_files') }}" />
              </div>
            @endif
            <div>
              @if ($user->can('delete', $comment))
                <x-ui.dropdown class="w-72" position="left">
                  <x-slot name="trigger">
                    <x-ui.icon name="ellipsis-v" class="cursor-pointer" />
                  </x-slot>
                  <div class="w-72">
                    <x-ui.confirm-modal :route="route('my.comments.comments.destroy', ['comment' => $comment->id])"
                                        method="delete"
                                        :label="__('comments.delete_comment')"
                                        :confirmation="__('actions.delete_confirmation')">
                      <div @click="open = true"
                           class="cursor-pointer text-libryo-gray-700 hover:bg-libryo-gray-100 group flex items-center px-4 py-2 text-sm"
                           role="menuitem"
                           tabindex="-1">
                        <x-ui.icon name="trash-alt" class="mr-5" />
                        {{ __('comments.delete_comment') }}
                      </div>

                      <x-slot:body>
                        @if($redirect)
                          <input type="hidden" name="save_and_back" value="{{ $redirect }}"/>
                        @endif
                      </x-slot:body>

                    </x-ui.confirm-modal>

                  </div>
                </x-ui.dropdown>

              @endif

            </div>
          </div>
        </div>
        <div>

          <div class="text-sm">{!! updateHyperlinks($comment->comment) !!}</div>

          @if(!$comment->place_id)
            <div class="text-xs text-libryo-gray-400 italic mt-2">{{ __('comments.added_to_all_streams') }}</div>
          @endif
        </div>
      </div>

    </div>

    <div class="grid justify-items-end">
      <div class="italic text-libryo-gray-400 text-xs">
        <x-ui.timestamp :timestamp="$comment->created_at" />
      </div>
    </div>



  </div>

  @if ($comment->files_count)
    <div @click="showingFiles = !showingFiles"
         class="py-1 bg-libryo-gray-50 text-center hover:bg-libryo-gray-100 cursor-pointer text-primary">
      {{ trans_choice('comments.comment_files_count', $comment->files_count, ['value' => $comment->files_count]) }}
    </div>
  @endif

  <div x-show="showingFiles" class="ml-12 p-3 bg-libryo-gray-50 mt-4">
    <turbo-frame loading="lazy"
                 src="{{ route('my.drives.files.for.comment.index', ['comment' => $comment]) }}"
                 id="files-for-comment-{{ $comment->id }}">
      <x-ui.skeleton />
    </turbo-frame>
  </div>

  @if (!$reply && $comment->comments_count > 0)
    <div @click="showingReplies = !showingReplies"
         class="mt-1 py-1 bg-libryo-gray-50 text-center hover:bg-libryo-gray-100 cursor-pointer text-primary">
      {{ trans_choice('comments.comment_replies_count', $comment->comments_count, ['value' => $comment->comments_count]) }}
    </div>
  @endif

  @if (!$reply)
    <div x-show="showingReplies" class="ml-12 p-3 bg-libryo-gray-50 mt-4">
      <turbo-frame src="{{ route('my.comments.for.commentable', ['type' => 'comment', 'id' => $comment->id]) }}"
                   loading="lazy" id="comments-for-comment-{{ $comment->id }}">
        <x-ui.skeleton />
      </turbo-frame>
    </div>
  @endif
</div>
