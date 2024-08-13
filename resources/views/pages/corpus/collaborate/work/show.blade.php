<div class="mb-8">
  <div class="font-semibold">{{ $resource->title }}</div>
  <div class="text-sm text-libryo-gray-700 italic">{{ $resource->title_translation }}</div>
</div>


<x-ui.tabs :active="request('tab')">

  <x-slot name="nav">
    <x-ui.tab-nav name="details">{{ __('corpus.work.show_title') }}</x-ui.tab-nav>
    <x-ui.tab-nav name="source">{{ __('corpus.work.source_document') }}</x-ui.tab-nav>

    @can('collaborate.corpus.work-expression.viewAny')
      <x-ui.tab-nav name="expressions">{{ __('corpus.work.expressions') }}</x-ui.tab-nav>
    @endcan

    @canany(['collaborate.corpus.work.view-children', 'collaborate.corpus.work.detach', 'collaborate.corpus.work.attach'])
      <x-ui.tab-nav name="children">{{ __('corpus.work.children') }}</x-ui.tab-nav>
    @endcanany

    @canany(['collaborate.corpus.work.view-parents', 'collaborate.corpus.work.detach', 'collaborate.corpus.work.attach'])
      <x-ui.tab-nav name="parents">{{ __('corpus.work.parents') }}</x-ui.tab-nav>
    @endcanany


    @can('collaborate.corpus.reference.versions.index')
      <x-ui.tab-nav name="citations">{{ __('corpus.reference.citations') }}</x-ui.tab-nav>
    @endcan

    @if($resource->maintainingUpdates?->isNotEmpty())
      <x-ui.tab-nav name="maintaining_updates">{{ __('notify.legal_update.maintaining_updates') }}</x-ui.tab-nav>
    @endif

    @if($resource->maintainingDocs?->isNotEmpty())
      <x-ui.tab-nav name="maintaining_docs">{{ __('notify.legal_update.maintaining_docs') }}</x-ui.tab-nav>
    @endif

  </x-slot>

  <x-ui.tab-content name="details">
    <x-turbo::frame loading="lazy" :id="$resource" :src="route('collaborate.works.show', $resource->id)">
      <x-ui.skeleton />
    </x-turbo::frame>
  </x-ui.tab-content>

  <x-ui.tab-content name="source">
    <div class="w-full h-full mt-4">
      <embed src="{{ route('collaborate.works.source.preview', $resource->id) }}" class="w-full"
             style="height:calc(100vh - 30rem)">
    </div>
  </x-ui.tab-content>

  @can('collaborate.corpus.work-expression.viewAny')
  <x-ui.tab-content name="expressions">
    <div class="no-shadow">
      @can('collaborate.corpus.work-expression.create')
        <div class="flex justify-end mt-2">
          <x-ui.button theme="primary" type="link" href="{{ route('collaborate.work-expressions.create', ['work' => $resource->id]) }}">
            {{ __('actions.create') }}
          </x-ui.button>
        </div>
      @endcan
      <x-turbo::frame target="_top" loading="lazy" :id="[$resource, 'expressions']" :src="route('collaborate.work-expressions.index', $resource->id)">
        <x-ui.skeleton />
      </x-turbo::frame>
    </div>
  </x-ui.tab-content>
  @endcan


  @canany(['collaborate.corpus.work.view-children', 'collaborate.corpus.work.detach', 'collaborate.corpus.work.attach'])
  <x-ui.tab-content name="children">
    <div class="no-shadow">
      @can('collaborate.corpus.work.attach')
        <div class="flex justify-end mt-4">
          <x-ui.button href="{{ route('collaborate.works.children.create', $resource->id) }}" type="link"
                       @click="open = true" theme="primary" class="tippy"
                       data-tippy-content="{{ __('corpus.work.attch_work') }}">
            <x-ui.icon name="plus" size="4" />
          </x-ui.button>
        </div>
      @endcan

      <x-turbo::frame loading="lazy" :id="[$resource, 'children']" :src="route('collaborate.works.children.index', $resource->id)">
        <x-ui.skeleton />
      </x-turbo::frame>
    </div>
  </x-ui.tab-content>
  @endcanany

  @canany(['collaborate.corpus.work.view-parents', 'collaborate.corpus.work.detach', 'collaborate.corpus.work.attach'])
  <x-ui.tab-content name="parents">
    <div class="no-shadow">
      @can('collaborate.corpus.work.attach')
        <div class="flex justify-end mt-4">
          <x-ui.button href="{{ route('collaborate.works.parents.create', $resource->id) }}" type="link"
                       @click="open = true" theme="primary" class="tippy"
                       data-tippy-content="{{ __('corpus.work.attch_work') }}">
            <x-ui.icon name="plus" size="4" />
          </x-ui.button>
        </div>
      @endcan

      <x-turbo::frame loading="lazy" :id="[$resource, 'parents']" :src="route('collaborate.works.parents.index', $resource->id)">
        <x-ui.skeleton />
      </x-turbo::frame>
    </div>
  </x-ui.tab-content>
  @endcanany


  @can('collaborate.corpus.reference.versions.index')
  <x-ui.tab-content name="citations">
    <div class="no-shadow">
      <x-turbo::frame loading="lazy" :id="[$resource, 'references']" :src="route('collaborate.corpus.works.references.index', $resource->id)">
        <x-ui.skeleton />
      </x-turbo::frame>
    </div>
  </x-ui.tab-content>
  @endcan

  @if($resource->maintainingUpdates?->isNotEmpty())
    <x-ui.tab-content name="maintaining_updates">
      <div class="pt-4 no-shadow">
        <x-ui.table no-search :rows="$resource->maintainingUpdates">
          <x-slot:head>
            <x-ui.th>{{ __('corpus.work.title') }}</x-ui.th>
          </x-slot:head>

          <x-slot:body>
            @foreach($resource->maintainingUpdates as $update)
              <x-ui.tr :loop="$loop">
                <x-ui.td>
                  @can('collaborate.notify.legal-update.view')
                    <a href="{{ route('collaborate.legal-updates.show', ['legal_update' => $update->id]) }}" class="text-primary">
                      <span class="flex flex-col w-full">
                        <span>{{ $update->title }}</span>
                        <span class="text-libryo-gray-600 font-normal">{{ $update->title_translation }}</span>
                      </span>
                    </a>
                  @else
                    <span class="flex flex-col w-full">
                      <span>{{ $update->title }}</span>
                      <span class="text-libryo-gray-600 font-normal">{{ $update->title_translation }}</span>
                    </span>
                  @endcan
                </x-ui.td>
              </x-ui.tr>
            @endforeach
          </x-slot:body>
        </x-ui.table>
      </div>
    </x-ui.tab-content>
  @endif


  @if($resource->maintainingDocs?->isNotEmpty())
    <x-ui.tab-content name="maintaining_docs">
      <div class="pt-4 no-shadow">
        <x-ui.table no-search :rows="$resource->maintainingDocs">
          <x-slot:head>
            <x-ui.th>{{ __('corpus.work.title') }}</x-ui.th>
            <x-ui.th>{{ __('corpus.doc.preview') }}</x-ui.th>
          </x-slot:head>

          <x-slot:body>
            @foreach($resource->maintainingDocs as $doc)
              <x-ui.tr :loop="$loop">
                <x-ui.td>
                  @can('collaborate.corpus.doc.view')
                    <a href="{{ route('collaborate.corpus.docs.show', ['doc' => $doc->id]) }}" class="text-primary">
                      <span class="flex flex-col w-full">
                        <span>{{ $doc->title }}</span>
                        <span class="text-libryo-gray-600 font-normal">{{ $doc->docMeta->title_translation ?? '-' }}</span>
                      </span>
                    </a>
                  @else
                    <span class="flex flex-col w-full">
                      span>{{ $doc->title }}</span>
                    <span class="text-libryo-gray-600 font-normal">{{ $doc->docMeta->title_translation ?? '-' }}</span>
                    </span>
                  @endcan
                </x-ui.td>

                <x-ui.td>
                  <a target="_blank" class="text-primary" href="{{ route('collaborate.corpus.docs.preview', ['doc' => $doc->id]) }}">
                    {{ __('corpus.doc.preview') }}
                  </a>
                </x-ui.td>
              </x-ui.tr>
            @endforeach
          </x-slot:body>
        </x-ui.table>
      </div>
    </x-ui.tab-content>
  @endif

</x-ui.tabs>
