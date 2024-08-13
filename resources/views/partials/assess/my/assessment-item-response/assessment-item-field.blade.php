<div>
  @if ($assessmentItem)
    <div class="flex items-center group">
      <div class="shrink-0 flex items-center pt-0.5 mr-2">
        @include('bookmarks.bookmark-icon', ['bookmarks' => $assessmentItem->bookmarks, 'turboKey' => "assess-{$assessmentItem->id}", 'routePrefix' => 'my.assess.bookmarks', 'routePayload' => ['item' => $assessmentItem->id]])
      </div>

      <div class="flex-grow flex flex-row items-center">
        <div class="flex-grow">
          <div class="text-base">
            {{-- <x-ui.modal>
              <x-slot name="trigger">
                <div @click="open = true"
                     class="cursor-pointer hover:text-primary">
                  @include(
                      'partials.assess.assessment-item.risk-rating-description',
                      ['assessmentItem' => $assessmentItem]
                  )
                </div>
              </x-slot>

              <div data-test="assessment-item-response-relations" class="" style="width: 85vw;">
                <turbo-frame id="assessment-item-response-relations-{{ $assessmentItemResponse->id }}"
                             src="{{ route('my.assess.assessment-item-responses.show.relations', ['aiResponse' => $assessmentItemResponse]) }}"
                             loading="lazy">
                  <x-ui.skeleton />
                </turbo-frame>
              </div>

              <div class="mt-5 grid justify-items-end">
                <a href="{{ route('my.assess.assessment-item.show', ['assessmentItem' => $assessmentItem]) }}"
                   class="text-primary hover:text-primary-darker">
                  <x-ui.icon name="link" />
                </a>
              </div>

            </x-ui.modal> --}}
            <div class="cursor-pointer hover:text-primary">
              <a data-turbo-frame="_top"
                 href="{{ $linkRoute }}"
                 class="{{ $assessmentItem->deleted_at ? 'text-libryo-gray-500' : 'text-primary' }} hover:text-primary-darker flex-grow"
              >
                @include(
                    'partials.assess.assessment-item.risk-rating-description',
                    ['assessmentItem' => $assessmentItem]
                )
              </a>
            </div>


          </div>
          @include(
              'partials.assess.assessment-item.domain-breadcrumbs',
              ['assessmentItem' => $assessmentItem]
          )
          @if ($withLibryo)
            <div class="text-primary-darker text-sm">
              {{ $assessmentItemResponse->libryo->title }}
            </div>
          @else
          @endif
        </div>
        @if($assessmentItem->relationLoaded('author') && $assessmentItem->author)
          <div class="shrink-0 text-libryo-gray-500 hover:text-libryo-gray-800 tippy" data-tippy-content="{{ __('tasks.created_by') }} {{ $assessmentItem->author->full_name }}">
            <x-ui.icon name="user" />
          </div>
        @endif
      </div>
    </div>
  @else
    <div class="italic text-base">
      {{ __('assess.assessment_item_response.no_assessment_item') }}
    </div>

  @endif

</div>
