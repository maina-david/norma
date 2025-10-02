<div x-data="{
  submitting: false,
  commentContent:'',
  showingUserSelector: false,
  showingEmojis: false,
  handleEmojiClick: function(detail) {
    $refs.commentBox.innerHTML = $refs.commentBox.innerHTML + detail.unicode;
    window.placeCaretAtEnd($refs.commentBox);
  },
  submitForm: window._debounce(function() {
    $refs.commentHtmlContent.innerHTML = $refs.commentBox.innerHTML;
     $refs.commentHtmlContent.querySelectorAll('.user-mention').forEach(function(span) {
      const userNode = document.createTextNode('**' + span.getAttribute('id') + '**');
      span.parentNode.insertBefore(userNode, span);
      span.remove();
    });
    $refs.commentContent.value = $refs.commentHtmlContent.innerHTML;
    $refs.commentForm.requestSubmit();
    this.submitting = false;
  }, 1500),
  handleSubmission: function() {
    this.submitting = true;
    this.submitForm();
  },
  handleAtMention: function() {
    this.showingUserSelector = true;
    setTimeout(function() {
      $refs.userSelector.tomselect.clear(true);
      $refs.userSelector.tomselect.focus();
    }, 100);
  },
  handleAtMentionSelect: function(e) {
    this.showingUserSelector = false;
    window.Norma.comments.comment.replaceMentions($refs.commentBox, e.target);
    window.placeCaretAtEnd($refs.commentBox);
  }
  }"
     class="flex items-start space-x-4">
  <div class="shrink-0">
    <x-ui.user-avatar :user="$user" />
  </div>
  <div class="min-w-0 flex-1">
    <x-ui.form
      x-ref="commentForm"
      :action="route('my.comments.for.commentable.store', ['type' => $commentableType, 'id' => $commentableId])"
      method="post"
      class="relative"
    >

      @if($normaId)
        <input type="hidden" name="target_norma_id" value="{{ $normaId }}"/>
      @endif

      @if($redirect)
        <input type="hidden" name="save_and_back" value="{{ $redirect }}"/>
      @endif
      <div
           class="border border-norma-gray-300 rounded-lg shadow-sm overflow-hidden focus-within:border-primary focus-within:ring-1 focus-within:ring-primary">
        <label for="comment" class="sr-only">Add your comment</label>

        <div
             x-ref="commentBox"
             x-model="commentContent"
             @keydown="if($event.key === '@') { handleAtMention(); } else if(!['Shift', 'Control', 'Alt', 'Meta'].includes($event.key)) showingUserSelector = false;"
             max-length="5000"
             contenteditable="true"
             class="full-width block w-full font-normal px-3 py-4 border-0 resize-none outline-none focus:ring-0 sm:text-sm bg-white">
        </div>
        <div x-show="showingUserSelector" class="">
          <div class="p-1 border border-norma-gray-50 bg-norma-gray-50 flex justify-end text-norma-gray-500">
            <x-ui.icon @click="showingUserSelector = false" name="times" size="3" class="cursor-pointer" />
          </div>
          <x-auth.user.my.user-selector x-ref="userSelector"
                                        @change="handleAtMentionSelect($event)"
                                        :value="null"
                                        class="border-none" />
        </div>

        <div x-show="showingEmojis">
          <div class="p-1 border border-norma-gray-50 bg-norma-gray-50 flex justify-end text-norma-gray-500">
            <x-ui.icon @click="showingEmojis = false" name="times" size="3" class="cursor-pointer" />
          </div>
          <emoji-picker class="w-full" x-on:emoji-click="handleEmojiClick($event.detail)"></emoji-picker>
        </div>

        {{-- Spacer element to match the height of the toolbar --}}
        <div class="py-2" aria-hidden="true">
          {{-- Matches height of button in toolbar (1px border + 36px content height) --}}
          <div class="py-px">
            <div class="h-9"></div>
          </div>
        </div>
      </div>

      <div class="absolute bottom-0 inset-x-0 pl-3 pr-2 py-2 flex justify-between">
        <div class="flex items-center space-x-5">
          {{-- <div class="flex items-center">
            <button type="button"
                    class="-m-2.5 w-10 h-10 rounded-full flex items-center justify-center text-norma-gray-400 hover:text-norma-gray-500">
              <x-ui.icon name="paperclip" />
              <span class="sr-only">Attach a file</span>
            </button>
          </div> --}}
          <div class="flex items-center">
            <button @click="showingEmojis = !showingEmojis" type="button"
                    class="-m-2.5 w-10 h-10 rounded-full flex items-center justify-center text-norma-gray-400 hover:text-norma-gray-500">
              <x-ui.icon name="smile-wink" />
            </button>
          </div>
          {{-- <div class="flex items-center">
            <button type="button"
                    class="-m-2.5 w-10 h-10 rounded-full flex items-center justify-center text-norma-gray-400 hover:text-norma-gray-500">
              <x-ui.icon name="at" />
            </button>
          </div> --}}
        </div>

        @isset($footer)
          {{ $footer }}
        @endisset

        <div class="shrink-0">
          <x-ui.button @click="handleSubmission" type="button" theme="primary" x-bind:disabled="submitting">
            <span x-show="!submitting">{{ $postText ?? __('comments.post') }}</span>
            <span x-show="submitting">{{ $postText ?? __('comments.posting') }}...</span>
          </x-ui.button>
        </div>
      </div>

      <div class="hidden" x-ref="commentHtmlContent"></div>
      <input class="hidden" name="comment" x-ref="commentContent" />
    </x-ui.form>
  </div>
</div>
