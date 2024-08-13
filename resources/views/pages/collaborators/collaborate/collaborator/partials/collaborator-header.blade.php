<div class="mb-4 -m-10 rounded-t-lg bg-primary">
  <div class="flex flex-col md:flex-row items-center bg-gray-100 bg-opacity-90 p-10 rounded-t-lg">
    <div class="flex items-center shrink-0">
      <div class="flex flex-col items-center space-y-4">
        <div class="shrink-0">
          {{-- h-24 w-24--}}
          <x-ui.user-avatar class="shrink-0 object-cover" :dimensions="24" :user="$resource" />
        </div>

        @if($resource->active && auth()->id() !== $resource->id)
          @can('collaborate.collaborators.collaborator.impersonate')
            <form action="{{ route('collaborate.collaborator.impersonate', $resource->id) }}" method="POST">
              @csrf
              <x-ui.button type="submit">
                {{ __('auth.user.impersonate') }}
              </x-ui.button>
            </form>
          @endcan
        @endif
      </div>

      <div class="ml-8 bg-white bg-opacity-70 px-6 pt-6 rounded-lg relative">
        @if ($resource->profile->nationality)
          <div class="absolute -left-2 -top-2">
            <x-ui.country-flag class="h-8 rounded-full" :countryCode="$resource->profile->nationality" />
          </div>
        @endif

        <div class="text-center font-semibold text-lg">{{ $resource->full_name }}</div>
        <div class="text-center text-xs">
          {!! __('collaborators.collaborator.joined', ['duration' => sprintf('<span class="text-xs" data-timestamp="%d" data-type="diff"></span>', $resource->created_at->timestamp)]) !!}
        </div>

        <div class="my-4 flex flex-col items-center">
          <div class="text-xl">{{ number_format($resource->score, 2) }}</div>
          <x-ui.rating-line
            text-class="text-xl font-semibold text-primary"
            no-text
            :value="$resource->score"
            class="ml-2 h-6 w-6"
          />

          <div class="text-center text-sm mt-5">
            {{ $resource->completed_tasks_count }} {{ __('nav.tasks') }}
          </div>

          <div>
            {!! __('collaborators.collaborator.turk_level', ['level' => ceil(($resource->completed_tasks_count ?? 1) / 100)]) !!}
          </div>
        </div>

      </div>
    </div>

    <div class="grow-1 md:ml-8 mt-4 md:mt-0 flex flex-wrap w-full">
      @foreach ($resource->ratingAverage->chunk(3) as $chunk)

        <div class="w-full md:w-1/2 xl:w-1/3">
          @foreach ($chunk as $rating)
            @if($rating->type)
              <div class="mb-2">
                <div>
                  <a
                      class="text-primary text-sm font-semibold"
                      href="{{ $rating->type->course_link ?? '#' }}"
                      target="_blank"
                  >
                    {{ $rating->type->name }}
                  </a>
                </div>

                <x-ui.rating-line :value="$rating->score" />
              </div>
            @endif
          @endforeach
        </div>

      @endforeach
    </div>
  </div>
</div>
