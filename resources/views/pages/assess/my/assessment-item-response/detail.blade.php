@php
  use App\Enums\Assess\ResponseStatus;
  use App\Enums\Assess\RiskRating;

  $exclude = [
    route('my.assess.create', [], false),
    route('my.assess.edit', ['assess' => $assessmentItem->hash_id], false),
  ];

  $explanation = $assessmentItem->explanationForNorma($norma);
@endphp
<x-layouts.app>

  <x-slot name="header">
    <div class="flex items-center">
      <div class="mr-6 -mt-1">
        <x-ui.back-referrer-button fallback="{{ route('my.assess.assessment-item-responses.index') }}" :exclude="$exclude"/>
      </div>

      <div class="flex items-center">
        <div>
          <span class="text-lg font-semibold">{{ $assessmentItem->toDescription() }}</span>
        </div>
      </div>
    </div>
  </x-slot>

  <x-slot name="actions">

    <div class="flex flex-row justify-between items-center space-x-4">
      <div class="flex justify-between items-center space-x-4" id="response_{{ $response->hash_id }}_actions">
        @if(!$assessmentItem->deleted_at)

          @if($assessmentItem->organisation_id === $organisation->id)

            @if(!$answered)
              <x-ui.delete-button route="{{ route('my.assess.destroy', ['assess' => $assessmentItem->hash_id]) }}">
                {{ __('actions.delete') }}
              </x-ui.delete-button>
            @endif

            <x-ui.button type="link" styling="outline" theme="primary" href="{{ route('my.assess.edit', ['assess' => $assessmentItem->hash_id]) }}">
              {{ __('actions.edit') }}
            </x-ui.button>
          @endif

          @include('bookmarks.bookmark-button', ['bookmarks' => $assessmentItem->bookmarks, 'turboKey' => "assess-{$assessmentItem->id}", 'routePrefix' => 'my.assess.bookmarks', 'routePayload' => ['item' => $assessmentItem->id]])

        @endif
      </div>

      <a target="_blank" href="https://success.norma.com/en/knowledge/norma-assess">
        <x-ui.icon name="question-circle"/>
      </a>
    </div>
  </x-slot>

  @if($explanation)
    <div class="mb-4 text-sm italic">
      <x-ui.collaborate.wysiwyg-content :content="$explanation->description" />
    </div>
  @endif

  <div class="mb-4">
    <span class="text-sm text-norma-gray-600 font-semibold">{!! __('assess.assessment_item_response.changes_only_for_stream') !!}</span>
  </div>


  <div class="mb-10">
    <div class="w-full bg-white py-8 px-4 sm:p-10 shadow" id="response_{{ $response->hash_id }}_table">
      @include('partials.assess.my.assessment-item-response.detail-table')
    </div>
  </div>

  @include(
      'partials.assess.my.assessment-item-response.relations',
      [
          'assessmentItem' => $assessmentItem,
          'assessmentItemResponse' => $response,
      ]
  )

</x-layouts.app>
