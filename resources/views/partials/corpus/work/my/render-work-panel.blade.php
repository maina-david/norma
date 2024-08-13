{{-- <x-corpus.work.work-panel :work="$work" :show-type="true">

  <div class="flex justify-end ">
    <a turbo-frame="_top" href="{{ route('my.corpus.works.show', ['work' => $work->id, 'view' => 'text']) }}">
      <x-ui.button theme="tertiary" styling="flat">
        {{ __('corpus.work.view_requirements_document') }}
        <x-ui.icon name="chevron-right" size="3" class="ml-3" />
      </x-ui.button>
    </a>
  </div>


  <div class="text-lg font-bold mb-5 ml-5">
    {{ __('corpus.reference.requirements') }}
  </div>

  <turbo-frame src="{{ route('my.references.for.requirements', ['work' => $work->id, ...$filters]) }}" loading="lazy"
               id="corpus-references-for-work-{{ $work->id }}">
    <x-ui.skeleton :rows="2" />
  </turbo-frame>
</x-corpus.work.work-panel> --}}
<a href="{{ route('my.corpus.works.show', ['work' => $work]) }}" class="cursor-pointer grid grid-cols-12 gap-4 my-2">

  <div class="col-span-10">
    <div class="text-sm">
      <div class="inline-block mr-2 rounded bg-libryo-gray-200 text-libryo-gray-700 px-1.5 py-0.5 text-xs">
        <div class="flex flex-row items-center">
          <x-ui.icon name="file-alt" size="3" class="mr-1" />
          {{ __('corpus.work.types.' . $work->work_type) }}
        </div>
      </div>

      <span class="text-primary">{{ $work->title }}</span>
      @if ($work->title_translation)
        <span class="text-libryo-gray-500 text-sm">[{{ $work->title_translation }}]</span>
      @endif
      @foreach ($work->parents as $parent)
        <div class="text-libryo-gray-600 mt-1 text-xs">{{ $parent->title }}</div>
      @endforeach

    </div>
  </div>
  <div class="col-span-2 flex flex-row justify-end items-center">
    <div>
      <x-corpus.work.my.work-flags :work="$work" :show-multiple-flags="false" size="6" />
    </div>
    {{-- <x-ui.icon x-cloak name="chevron-right" size="3" /> --}}
  </div>
</a>
