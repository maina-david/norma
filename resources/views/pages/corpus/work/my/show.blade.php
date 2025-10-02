@php use App\Enums\Corpus\WorkStatus; @endphp
<x-layouts.app>
  <x-slot name="header">
    <div class="flex items-center -my-2">
      <div class="mr-6 -mt-1">
        <x-ui.back-referrer-button fallback="{{ route('my.corpus.requirements.index') }}" />
      </div>
      <div class="mr-5">
        <x-corpus.work.my.work-flags :work="$work" :show-multiple-flags="true" />
      </div>
      <div class="flex items-center space-x-2">
        <span>{{ $work->title }}</span>
        @if (WorkStatus::inactive()->is($work->status))
          <x-ui.badge>{{ WorkStatus::inactive()->label() }}</x-ui.badge>
        @endif
      </div>
    </div>
  </x-slot>

  {{-- remove if not reverted after launch --}}
  {{-- @if ($preview)
    <div>
      <iframe
              id="work-document-preview"
              class="w-screen-90"
              style="height: 80vh;"
              src="{{ route('my.corpus.works.preview.source', ['work' => $work->id]) }}"></iframe>
    </div>
  @else
    <turbo-frame src="{{ $view === 'text'? route('my.works.full-text.show', ['work' => $work->id]): route('my.references.for.requirements', ['work' => $work->id]) }}"
                 loading="lazy"
                 id="work-requirements-or-full-text-{{ $work->id }}">
      <x-ui.skeleton :rows="2" />
    </turbo-frame>
  @endif --}}

  <div class="p-5 bg-white shadow">
    <x-ui.tabs :active="$view === 'text' || $preview ? 'document' : 'references'">

      <x-slot name="nav">
        @if (!$preview)
          <x-ui.tab-nav name="references">{{ __('corpus.reference.requirements') }}</x-ui.tab-nav>
        @endif
        <x-ui.tab-nav name="document">{{ __('corpus.work.requirements_document') }}</x-ui.tab-nav>
      </x-slot>

      <x-ui.tab-content name="document">
        @include('pages.corpus.work.my.work-content')
      </x-ui.tab-content>

      @if (!$preview)
        <x-ui.tab-content name="references">
          <turbo-frame src="{{ route('my.references.for.requirements', ['work' => $work->id]) }}"
                       loading="lazy"
                       id="corpus-references-for-work-{{ $work->id }}">
            <x-ui.skeleton :rows="2" />
          </turbo-frame>
        </x-ui.tab-content>
      @endif
    </x-ui.tabs>
  </div>


  <div class="grid lg:grid-cols-12 lg:gap-4 bg-norma-gray-50 border-t border-norma-gray-100 p-5">
    {{-- LEFT SIDE --}}
    <div class="lg:col-span-7">
      <x-ui.tabs x-cloak>
        <x-slot name="nav">
          @if (!$work->children->isEmpty())
            <x-ui.tab-nav name="child_documents">
              {{ __('corpus.work.subsidiary_documents') }}
            </x-ui.tab-nav>
          @endif

          @if (!$work->parents->isEmpty())
            <x-ui.tab-nav name="parent_documents">
              {{ __('corpus.work.parent_documents') }}
            </x-ui.tab-nav>
          @endif
        </x-slot>

        @if (!$work->children->isEmpty())
          <x-ui.tab-content name="child_documents">
            <div class="pt-5 divide-y divide-norma-gray-100">
              @foreach ($work->children as $child)
                <a class="block text-primary mb-3" turbo-frame="_top"
                   href="{{ route('my.corpus.works.show', ['work' => $child->id]) }}">
                  <x-corpus.work.work-type :work="$child" />
                  <span class="text-sm font-semibold">{{ $child->title }}</span>
                </a>
              @endforeach
            </div>
          </x-ui.tab-content>
        @endif

        @if (!$work->parents->isEmpty())
          <x-ui.tab-content name="parent_documents">
            <div class="pt-5 divide-y divide-norma-gray-100">
              @foreach ($work->parents as $parent)
                <a class="block text-primary mb-3" turbo-frame="_top"
                   href="{{ route('my.corpus.works.show', ['work' => $parent->id]) }}">
                  <x-corpus.work.work-type :work="$parent" />
                  <span class="text-sm font-semibold">{{ $parent->title }}</span>
                </a>
              @endforeach
            </div>
          </x-ui.tab-content>
        @endif
      </x-ui.tabs>
    </div>

    {{-- RIGHT SIDE --}}
    <div class="lg:col-span-5">

    </div>
  </div>


</x-layouts.app>
