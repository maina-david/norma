<div>
  @foreach($rows as $row)
    <div
      x-data="{
        openFrame: function () {
          var container = document.querySelector('#corpus-work-for-reference-{{ $row->id }}');
          var frame = document.querySelector('#corpus-work-for-reference-{{ $row->id }} turbo-frame');
          frame.removeAttribute('id');
          frame.setAttribute('class', 'h-full overflow-hidden block');
          frame.setAttribute('src', container.getAttribute('data-src'));
        },
        resetFrame: function () {
          var frame = document.querySelector('#corpus-work-for-reference-{{ $row->id }} turbo-frame');
          frame.removeAttribute('id');
          frame.removeAttribute('class');
          frame.removeAttribute('src');
          frame.innerHTML = '';
        }
      }"
    >
      <x-ui.modal teleport @closed="resetFrame">
        <x-slot name="trigger">
          <div class="flex items-center cursor-pointer" @click="open = true; openFrame();">
            <div class="text-sm flex-grow">
              <div class="text-primary font-semibold">{{ $row->refPlainText->plain_text }}</div>
              <div class="text-libryo-gray-600">{{ $row->work->title }}</div>
              <div class="italic text-xs text-libryo-gray-600">{{ $row->legalDomains->pluck('title')->join(', ') }}</div>
            </div>
            <div class="flex-shrink-0">
              <x-ui.country-flag class="w-8 h-8 rounded-full" countryCode="{{ $row->work->primaryLocation->flag }}" />
            </div>
          </div>
        </x-slot>

        <div class="mx-auto w-full md:w-screen-75 overflow-hidden max-w-[90vw] h-full max-h-[80vh] flex flex-col">
          <div class="flex-shrink-0 border-b border-libryo-gray-200 mb-4">
            <div class="font-bold">{{ $row->work->title }}</div>
            <div class="italic text-xs text-libryo-gray-600 my-2">{{ $row->legalDomains->pluck('title')->join(', ') }}</div>
          </div>

          <div id="corpus-work-for-reference-{{ $row->id }}" class="flex-grow overflow-hidden pb-8" data-src="{{ route('my.corpus.works.for-reference.show', ['reference' => $row->id]) }}">
            <turbo-frame class="h-full overflow-hidden block" loading="lazy">
              <x-ui.skeleton :rows="2" />
            </turbo-frame>
          </div>
        </div>
      </x-ui.modal>
    </div>
  @endforeach

  <div>
    {{ $rows->links() }}
  </div>
</div>
