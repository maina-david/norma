<div class="flex justify-center my-4">
  <div>
    @if($tasks->currentPage() !== $tasks->lastPage())
      <x-ui.button type="link" :href="$tasks->withQueryString()->nextPageUrl()">
        {{ __('actions.more') }}
      </x-ui.button>
    @endif
  </div>
</div>
