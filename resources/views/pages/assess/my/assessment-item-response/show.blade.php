<x-layouts.app>

  <x-slot name="header">
    <div class="flex items-center">
      <div class="mr-6 -mt-1">
        <x-ui.back-referrer-button fallback="{{ route('my.assess.assessment-item-responses.index') }}" />
      </div>

      <div class="flex items-center">
        <div><x-ui.icon name="clipboard-list" class="text-norma-gray-400" size="8" /></div>
        <div>{{ __('my.nav.assessment_items') }}</div>
      </div>
    </div>

    <div class="mt-8">
      <span class="text-base font-bold">{{ $assessmentItem->toDescription() }}</span>
    </div>
  </x-slot>

  <x-slot name="actions">

    <div class="flex flex-row justify-between items-center">
      @include('bookmarks.bookmark-button', ['bookmarks' => $assessmentItem->bookmarks, 'turboKey' => "assess-{$assessmentItem->id}", 'routePrefix' => 'my.assess.bookmarks', 'routePayload' => ['item' => $assessmentItem->id]])


      <a target="_blank" class="ml-4" href="https://success.norma.com/en/knowledge/norma-assess">
        <x-ui.icon name="question-circle" />
      </a>
    </div>
  </x-slot>

  <div class="mb-10">
    <x-assess.assessment-item-response.response-data-table
                                                           :base-query="$baseQuery"
                                                           :fields="$tableFields" />
  </div>

  @include(
      'partials.assess.my.assessment-item-response.relations',
      [
          'assessmentItem' => $assessmentItem,
          'assessmentItemResponse' => $assessmentItemResponse,
      ]
  )

</x-layouts.app>
