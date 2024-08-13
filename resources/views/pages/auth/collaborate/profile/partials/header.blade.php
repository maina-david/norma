<div class="flex">
  <div class="flex items-center flex-grow">
    <x-ui.user-avatar :user="$user" :dimensions="16" />
    <div class="ml-2">
      <div class="font-semibold text-2xl">{{ $user->full_name }}</div>
      <div class="text-sm">{{ __('auth.user.joined', ['duration' => $user->created_at->diffForHumans(now(), \Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW)])  }}</div>
    </div>
  </div>
  <div class="flex-shrink-0 flex flex-col justify-center items-center">
    <div>{{ __('auth.user.average_rating') }}</div>
    <x-ui.rating-star text-class="text-2xl font-semibold" :value="$user->score" class="h-6 w-6" />
  </div>
</div>

<div class="flex flex-wrap items-center mt-4">
  @foreach($user->ratingAverage as $rating)
    <div class="flex items-center space-x-2 rounded-full bg-primary-lighter  text-libryo-gray-50 py-1 px-4 mr-2 mt-2">
      @if($rating->type)
        <x-ui.icon :name="$rating->type->icon" />
      @endif
      <span>{{ $rating->type->name ?? '' }}</span>
      <x-ui.rating-star text-class="font-semibold text-libryo-gray-50 pr-1" :value="$rating->score" class="h-4 w-4  text-libryo-gray-50" />
    </div>
  @endforeach
</div>
