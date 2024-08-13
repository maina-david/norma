@php
use App\Enums\Workflows\TaskStatus;
@endphp

<x-layouts.collaborate>
  <div class="min-h-full">
    <div class="mx-auto px-4 sm:px-6 lg:px-8">
      <h1 class="sr-only">{{ __('auth.user.settings.profile') }}</h1>

      <div class="mb-6">
        <section aria-labelledby="profile-overview-title">
          <div class="rounded-lg bg-white overflow-hidden shadow">
            <h2 class="sr-only" id="profile-overview-title">{{ __('collaborators.profile.information') }}</h2>
            <div class="bg-white p-6">
              <div class="sm:flex sm:items-center sm:justify-between">
                <div class="sm:flex sm:space-x-5">
                  <div class="flex-shrink-0">
                    <img class="mx-auto h-20 w-20 rounded-full" src="{{ $collaborator->profile_photo_url }}" alt="">
                  </div>
                  <div class="mt-4 text-center sm:mt-0 sm:pt-1 sm:text-left">
                    <p class="text-sm font-medium text-libryo-gray-600">{{ __('collaborators.welcome_back') }},</p>
                    <p class="text-xl font-bold text-libryo-gray-900 sm:text-2xl">{{ $collaborator->full_name }}</p>
                    <p class="text-sm font-medium text-libryo-gray-600">
                      {!! __('collaborators.collaborator.turk_level', ['level' => ceil(($collaborator->completed_tasks_count ?? 1) / 100)]) !!}
                    </p>
                  </div>
                </div>
                <div class="mt-5 flex justify-center sm:mt-0">
                  <a href="{{ route('collaborate.profile.show') }}" class="flex justify-center items-center px-4 py-2 border border-libryo-gray-300 shadow-sm text-sm font-medium rounded-md text-libryo-gray-700 bg-white hover:bg-libryo-gray-50">
                    {{ __('collaborators.view_profile') }}
                  </a>
                </div>
              </div>
            </div>
            <div class="border-t border-libryo-gray-200 bg-libryo-gray-50 grid grid-cols-1 divide-y divide-libryo-gray-200 sm:grid-cols-4 sm:divide-y-0 sm:divide-x">
              <a class="px-6 py-5 text-sm font-medium text-center" href="{{ route('collaborate.dashboard', ['status' => [TaskStatus::todo()->value]]) }}">
                <span>{{ $collaborator->todo_tasks_count }}</span>
                <span>{{ trans_choice('collaborators.todo_task', $collaborator->todo_tasks_count) }}</span>
              </a>

              <a class="px-6 py-5 text-sm font-medium text-center" href="{{ route('collaborate.dashboard', ['status' => [TaskStatus::inProgress()->value]]) }}">
                <span>{{ $collaborator->in_progress_tasks_count }}</span>
                <span>{{ trans_choice('collaborators.in_progress_task', $collaborator->in_progress_tasks_count) }}</span>
              </a>

              <a class="px-6 py-5 text-sm font-medium text-center" href="{{ route('collaborate.dashboard', ['status' => [TaskStatus::inReview()->value]]) }}">
                <span>{{ $collaborator->in_review_tasks_count }}</span>
                <span>{{ trans_choice('collaborators.in_review_task', $collaborator->in_review_tasks_count) }}</span>
              </a>

              <a class="px-6 py-5 text-sm font-medium text-center" href="{{ route('collaborate.dashboard', ['status' => [TaskStatus::done()->value]]) }}">
                <span>{{ $collaborator->completed_tasks_count }}</span>
                <span>{{ trans_choice('collaborators.completed_task', $collaborator->completed_tasks_count) }}</span>
              </a>

            </div>
          </div>
        </section>
      </div>

        <div class="grid grid-cols-1 gap-4 items-start lg:grid-cols-4">

          <div class="grid grid-cols-1 gap-4" x-data="{}">
            <x-turbo::frame id="task-filters">
              @include('pages.collaborators.collaborate.dashboard.partials.filter-form')
            </x-turbo::frame>
          </div>

          <div class="grid grid-cols-1 gap-4 lg:col-span-3">
            <x-turbo::frame id="task-listing">
              @include('pages.collaborators.collaborate.dashboard.partials.task-listing')
            </x-turbo::frame>
          </div>

        </div>
      </div>
  </div>
</x-layouts.collaborate>
