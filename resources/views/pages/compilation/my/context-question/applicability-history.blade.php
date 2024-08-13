@php use App\Services\Storage\MimeTypeManager; @endphp
<x-layouts.app>
  <x-slot name="header">
    <div class="flex items-center">
      <x-ui.icon name="ballot-check" size="10" class="mr-4 text-libryo-gray-600" />
      <div>{{ __('compilation.context_question.applicability_change_history') }}</div>
    </div>
    <div class="text-libryo-gray-500 text-sm font-base mt-2">
      {{ __('compilation.context_question.applicability_change_history_description') }}
    </div>
  </x-slot>


  <x-compilation.context-question.my.layout bordered>

    <div>
      <div class="container mx-auto">
        <x-ui.card>

          <div>
            @foreach($activities as $activity)
              @include('partials.compilation.my.context-question.applicability-activity-item', ['activity' => $activity])
            @endforeach
          </div>

          <div class="mt-8">
            {{ $activities->links() }}
          </div>
        </x-ui.card>
      </div>
    </div>

  </x-compilation.context-question.my.layout>
</x-layouts.app>


