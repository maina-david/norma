@php use App\Models\Workflows\Board; @endphp
<div class="bg-white shadow overflow-hidden sm:rounded-lg">
  <div class="px-4 py-5 sm:px-6">
    <h3 class="text-lg leading-6 font-medium text-libryo-gray-900">
      <a href="{{ route('collaborate.corpus.docs.preview', ['doc' => $resource->id]) }}">{{ $resource->title }}</a>
    </h3>
    <p class="mt-1 max-w-2xl text-sm text-libryo-gray-500">
      {{ $resource->docMeta->title_translation }}
    </p>
  </div>
  <div class="border-t border-libryo-gray-200 px-4 py-5 sm:p-0">
    <dl class="sm:divide-y sm:divide-libryo-gray-200">
      <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
        <dt class="text-sm font-medium text-libryo-gray-500">
          {{ __('interface.id') }}
        </dt>
        <dd class="mt-1 text-sm text-libryo-gray-900 sm:mt-0 sm:col-span-2">
          {{ $resource->id }}
        </dd>
      </div>
      <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
        <dt class="text-sm font-medium text-libryo-gray-500">
          {{ __('corpus.doc.language') }}
        </dt>
        <dd class="mt-1 text-sm text-libryo-gray-900 sm:mt-0 sm:col-span-2">
          {{ $resource->docMeta->language_code }}
        </dd>
      </div>
      <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
        <dt class="text-sm font-medium text-libryo-gray-500">
          {{ __('arachno.source.source') }}
        </dt>
        <dd class="mt-1 text-sm text-libryo-gray-900 sm:mt-0 sm:col-span-2">
          @if ($resource->source)
            <a
              class="text-primary"
              href="{{ route('collaborate.sources.show', ['source' => $resource->source->id]) }}"
            >
              {{ $resource->source->title ?? '' }}
            </a>
          @endif
        </dd>
      </div>
      <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
        <dt class="text-sm font-medium text-libryo-gray-500">
          {{ __('corpus.doc.url') }}
        </dt>
        <dd class="mt-1 text-sm text-libryo-gray-900 sm:mt-0 sm:col-span-2">
          @if ($resource->docMeta->source_url)
            <a
              class="text-primary"
              href="{{ $resource->docMeta->source_url }}"
              target="_blank"
            >
              {{ $resource->docMeta->source_url }}
            </a>
          @endif
        </dd>
      </div>
      @if (!$resource->work)
        @can('collaborate.corpus.doc.generate-work')
          {{-- @if (!$resource->for_update) --}}
          <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
            <dt class="text-sm font-medium text-libryo-gray-500">
              {{ __('corpus.doc.work') }}
            </dt>
            <dd class="mt-1 text-sm text-libryo-gray-900 sm:mt-0 sm:col-span-2">
              <x-ui.form method="post"
                         :action="route('collaborate.corpus.docs.generate.work', ['doc' => $resource->id])">
                <x-ui.button type="submit" theme="primary">{{ __('corpus.doc.generate_work') }}</x-ui.button>
              </x-ui.form>
            </dd>
          </div>
          {{-- @endif --}}
        @endcan
      @else
        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
          <dt class="text-sm font-medium text-libryo-gray-500">
            {{ __('corpus.doc.work') }}
          </dt>
          <dd class="mt-1 text-sm text-libryo-gray-900 sm:mt-0 sm:col-span-2">
            <a class="text-primary"
               href="{{ route('collaborate.works.show', ['work' => $resource->work->id]) }}">{{ __('corpus.doc.view_linked_work') }}</a>
          </dd>
        </div>
      @endif

      @if (!$resource->work && $resource->legalUpdates->isEmpty())
        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
          <dt class="text-sm font-medium text-libryo-gray-500">
            {{ __('corpus.doc.legal_update') }}
          </dt>
          <dd class="mt-1 text-sm text-libryo-gray-900 sm:mt-0 sm:col-span-2">
            {{-- Generate update button here --}}
            <x-ui.form method="post"
                       :action="route('collaborate.corpus.docs.for-update.create-update', ['doc' => $resource->id])">
              <x-ui.button type="submit" theme="primary">{{ __('corpus.doc.generate_update') }}</x-ui.button>
            </x-ui.form>
          </dd>
        </div>
      @elseif(!$resource->work && $resource->legalUpdates->isNotEmpty())
        <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
          <dt class="text-sm font-medium text-libryo-gray-500">
            {{ __('corpus.doc.legal_update') }}
          </dt>
          <dd class="mt-1 text-sm text-libryo-gray-900 sm:mt-0 sm:col-span-2">
            <div class="mb-4">
              <a class="text-primary"
                 href="{{ route('collaborate.legal-updates.show', ['legal_update' => $resource->legalUpdates->first()->id]) }}">{{ __('corpus.doc.view_update_created_from') }}</a>
            </div>

            @if ($board = Board::forUpdate()->first()?->id)
              @can('collaborate.workflows.create-from-doc')
                <x-ui.form method="post"
                           :action="route('collaborate.tasks.wizard.store', ['board' => $board, 'stage' => 1])">
                  <input type="hidden" name="legal_update_id" value="{{ $resource->legalUpdates->first()->id }}">
                  <x-ui.button type="submit" theme="primary">{{ __('corpus.doc.generate_workflow') }}</x-ui.button>
                </x-ui.form>

                @error('legal_update_id')
                  <div class="mt-4 text-negative font-semibold">
                    {!! $message !!}
                  </div>
                @enderror
              @endcan
            @endif

          </dd>
        </div>
      @endif

    </dl>
  </div>
</div>
