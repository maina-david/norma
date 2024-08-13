<x-layouts.app>

  <x-slot name="header">
    <div class="flex">
      <div class="mr-6">
        <x-ui.back-referrer-button fallback="{{ route('my.corpus.requirements.index') }}" />
      </div>

      <div>
        <div class="text-base">{{ $reference->work->title }}</div>
        <div>{{ $reference->refPlainText?->plain_text }}</div>
      </div>
    </div>
  </x-slot>

  <x-slot name="actions">
    <div class="flex items-center">

      @include('bookmarks.bookmark-button', ['ref' => $reference, 'bookmarks' => $reference->bookmarks, 'turboKey' => "reference-{$reference->id}", 'routePrefix' => 'my.reference.bookmarks', 'routePayload' => ['reference' => $reference->id]])

      <div class="ml-4">
        <a href="{{ route('my.corpus.works.show', ['work' => $reference->work_id, 'view' => 'text']) }}">
          <x-ui.button theme="tertiary" styling="outline">
            {{ __('corpus.work.view_requirements_document') }}
          </x-ui.button>
        </a>
      </div>
    </div>
  </x-slot>

  <div class="bg-white shadow">
    @include('partials.corpus.reference.my.show')
  </div>
</x-layouts.app>
