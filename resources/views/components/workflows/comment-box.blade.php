@props(['name', 'contentField', 'routePrefix', 'routeParams', 'resource' => null])
<div class="mb-4">
  <div x-data="{
  {{ $name }}Content:'',
  showingEmojis: false,
  handleEmojiClick: function(detail) {
    $refs.{{ $name }}Box.innerHTML = $refs.{{ $name }}Box.innerHTML + detail.unicode;
    window.placeCaretAtEnd($refs.{{ $name }}Box);
  },
  handleSubmission: function() {
    $refs.{{ $name }}HtmlContent.innerHTML = $refs.{{ $name }}Box.innerHTML;
     $refs.{{ $name }}HtmlContent.querySelectorAll('.user-mention').forEach(function(span) {
      const userNode = document.createTextNode('**' + span.getAttribute('id') + '**');
      span.parentNode.insertBefore(userNode, span);
      span.remove();
    });
    $refs.{{ $name }}Content.value = $refs.{{ $name }}HtmlContent.innerHTML.trim();
    if ($refs.{{ $name }}Content.value.length < 2) {
      toast.error({ message: '{{ __('interface.please_enter_a_message') }}' });
      return;
    }
    $refs.{{ $name }}sForm.requestSubmit();
  },
  handlePaste: function (event) {
    setTimeout(function () {
      var holder = document.createElement('div');
      holder.innerHTML = event.target.innerHTML;
      holder.querySelectorAll('[style]').forEach(function (el) {
        el.setAttribute('style', el.getAttribute('style').replace(/--tw[^;]+;\s*/g, ''));
      });
      event.target.innerHTML = holder.innerHTML;
    }, 100);
  },
  }"
       class="flex items-start space-x-4"
  >
    <div class="min-w-0 flex-1">
      <x-ui.form x-ref="{{ $name }}sForm" :action="route($routePrefix . (isset($resource) ? '.update' : '.store'), $routeParams)" :method="isset($resource) ? 'put' : 'post'" class="relative">

        <div class="border border-libryo-gray-300 rounded-lg shadow-sm overflow-hidden focus-within:border-primary focus-within:ring-1 focus-within:ring-primary">
          <label for="{{ $name }}" class="sr-only">Add your message</label>

          <div @paste="handlePaste" x-ref="{{ $name }}Box" x-model="{{ $name }}Content" max-length="5000" contenteditable="true" class="full-width block w-full font-normal px-3 py-4 border-0 resize-none outline-none focus:ring-0 sm:text-sm bg-white">{!! $resource->{$contentField} ?? '' !!}</div>

          <div x-show="showingEmojis">
            <div class="px-4 mt-6 pb-2 flex text-libryo-gray-500">
              <button @click="showingEmojis = !showingEmojis" type="button" class="-m-2.5 w-10 h-10 rounded-full flex items-center justify-center text-libryo-gray-400 hover:text-libryo-gray-500">
                <x-ui.icon name="smile-wink" />
              </button>
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
            <div class="flex items-center">
              <button @click="showingEmojis = !showingEmojis" type="button" class="-m-2.5 w-10 h-10 rounded-full flex items-center justify-center text-libryo-gray-400 hover:text-libryo-gray-500">
                <x-ui.icon name="smile-wink" />
              </button>
            </div>
          </div>
          <div class="shrink-0">
            <x-ui.button @click="handleSubmission" type="button" theme="primary">
              {{ __('comments.post') }}
            </x-ui.button>
          </div>
        </div>

        <div class="hidden" x-ref="{{ $name }}HtmlContent"></div>
        <input class="hidden" name="{{ $name }}" x-ref="{{ $name }}Content" />
      </x-ui.form>

      @error($name)
      <p class="text-sm text-secondary">{{ $message }}</p>
      @enderror
    </div>
  </div>
</div>
