<div>
  <div class="mb-4 font-bold">
    {{ __('assess.risk_rating') }}:
    <span class="text-{{ $ratingColors[$risk] }}">
      {{ __('assess.assessment_item.risk_rating.' . $risk) }}
    </span>
  </div>

  <div class="flex items-center">
    <div class="shrink-0">
      <div class="rounded-md bg-libryo-gray-900 m-auto py-5 px-3 relative h-80 flex flex-col">
        @foreach ($riskRatings as $rating)
          <div
               class="bg-{{ $ratingColors[$rating] }} inline-block rounded-full w-16 h-16  opacity-{{ $getOpacityForRisk($risk, $rating) }} mb-6 relative m-auto">
          </div>
        @endforeach
      </div>
    </div>
    <div class="ml-5 w-0 flex-1">
      <div class="flex flex-col h-80 text-xs font-medium">
        @foreach ($ratingDescriptions as $rating => $ratingDescription)
          <dl class="my-auto md:h-24 border rounded rounded-sm border-{{ $getColorForRisk($risk, $rating) }} p-2 {{ $getVisibilityForRisk($risk, $rating) }}">
            @foreach ($ratingDescription as $langKey)

              <div class="sm:grid sm:grid-cols-2 sm:gap-2 ">
                <dt class=" text-libryo-gray-500 truncate">
                  {{ __('assess.risk_rating_descriptions.labels.' . $langKey) }}
                </dt>
                <dd>
                  <div class="text-libryo-gray-900 sm:text-right">
                    {{ $getLangString($rating, $langKey) }}
                  </div>
                </dd>
              </div>

            @endforeach
          </dl>
        @endforeach
      </div>
    </div>
  </div>

</div>
