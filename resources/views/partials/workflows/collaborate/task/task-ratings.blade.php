@php
$ratingTypes = $task->taskType->ratingGroup->ratingTypes ?? collect();
$jsRatings = array_map(fn () => 5, $ratingTypes->toArray())
@endphp
<div class="space-y-4 pb-8 mt-4" x-data="{appliedRating: {{ json_encode($jsRatings) }}}">
  @if($task->canRate())

    <div x-data="{rating:{{ json_encode($errors->first('new_task_status') === __('workflows.task.validations.task_requires_rating')) }}}">
      <div class="flex justify-center">
        <x-ui.button x-show="!rating" styling="outline" theme="primary" @click="rating = true">
          {{ __('workflows.task.rate') }}
        </x-ui.button>

        @if($task->user_id)
        <div x-show="rating" class="text-center space-y-4 mb-6">
          <div>{{ __('auth.user.average_rating') }}</div>
          <div class="text-2xl font-semibold" x-html="(appliedRating.reduce(function(a,b) { return a+b; }, 0)/{{ count($jsRatings) }}).toFixed(2)"></div>
        </div>

        @else
          <div x-show="rating">{{ __('workflows.task.cant_rate_unassigned_task')  }}</div>
        @endif
      </div>

      @if($task->user_id)
      <x-ui.form method="post" :action="route('collaborate.task.rate', ['task' => $task->id])" class="grid grid-cols-2 gap-4 mt-4 border-b border-libryo-gray-200 pb-6" x-show="rating">
        @foreach($ratingTypes as $index => $toRate)
          <div>
            <div class="flex items-center font-semibold">
              <span class="flex-shrink-0">
                <x-ui.icon :name="$toRate->icon" />
              </span>
              <span class="ml-2 flex-grow">{{ $toRate->name }}</span>
              <span class="flex-shrink-0">
                <x-ui.input @change="appliedRating[{{ $index }}] = parseFloat($event.target.value, 10)" value="5" class="mb-2" type="number" max="5" min="1" step="0.1" name="rating[{{ $toRate->id }}][score]" label="" :placeholder="__('workflows.task.rating')" />
              </span>
            </div>

            <x-ui.textarea rows="4" name="rating[{{ $toRate->id }}][comments]"></x-ui.textarea>
          </div>
        @endforeach

        <div class="flex justify-end col-span-2 space-x-4">
          <x-ui.button type="button" styling="outline" @click="rating = false">
            {{ __('actions.cancel') }}
          </x-ui.button>

          <x-ui.button type="submit" styling="outline" theme="primary">
            {{ __('workflows.task.rate') }}
          </x-ui.button>
        </div>
      </x-ui.form>
      @endif

    </div>
  @endif

  @foreach($ratings as $rating)
    <div class="p-4 border border-libryo-gray-200 rounded-lg">
      <div class="flex items-center justify-between">
        <div class="font-semibold">
          @if($rating->type->course_link)
            <a class="text-primary" href="{{ $rating->type->course_link }}" target="_blank">{{ $rating->type->name }}</a>
          @else
            <span>{{ $rating->type->name }}</span>
          @endif
        </div>
        <div class="text-negative font-bold">
          <span>{{ number_format($rating->score, 1) }}</span>
        </div>
      </div>
      <p class="text-libryo-gray-800">
        {{ $rating->comments }}
      </p>
    </div>
  @endforeach

  @if($ratings->isNotEmpty())
    @can('collaborate.workflows.task.delete-rating')
      <div class="flex items-center justify-center">
        <x-ui.delete-button route="{{ route('collaborate.task.ratings.delete', ['task' => $task->id]) }}">
          {{ __('workflows.task.delete_rating') }}
        </x-ui.delete-button>
      </div>
    @endcan
  @endif
</div>
