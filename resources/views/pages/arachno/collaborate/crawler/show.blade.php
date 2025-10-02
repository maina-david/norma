@php
  use App\Enums\Arachno\CrawlType;
@endphp

<div class="border-t border-norma-gray-200 px-4 py-5 sm:p-0">
  <dl class="sm:divide-y sm:divide-norma-gray-200">
    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
      <dt class="text-sm font-medium text-norma-gray-500">{{ __('arachno.crawler.title') }}</dt>
      <dd class="mt-1 text-sm text-norma-gray-900 sm:col-span-2 sm:mt-0">{{ $resource->title }}</dd>
    </div>

    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
      <dt class="text-sm font-medium text-norma-gray-500">{{ __('arachno.crawler.slug') }}</dt>
      <dd class="mt-1 text-sm text-norma-gray-900 sm:col-span-2 sm:mt-0">{{ $resource->slug }}</dd>
    </div>

    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
      <dt class="text-sm font-medium text-norma-gray-500">{{ __('interface.description') }}</dt>
      <dd class="mt-1 text-sm text-norma-gray-900 sm:col-span-2 sm:mt-0">{!! nl2br($resource->description) !!}</dd>
    </div>

    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
      <dt class="text-sm font-medium text-norma-gray-500">{{ __('arachno.url_frontier_link.source') }}</dt>
      <dd class="mt-1 text-sm text-norma-gray-900 sm:col-span-2 sm:mt-0">
        @if ($resource->source)
          <a class="text-primary"
             href="{{ route('collaborate.sources.show', ['source' => $resource->source_id]) }}">
            {{ $resource->source?->title }}
          </a>
        @endif
      </dd>
    </div>
    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
      <dt class="text-sm font-medium text-norma-gray-500">{{ __('arachno.crawler.schedule') }}</dt>
      <dd class="mt-1 text-sm text-norma-gray-900 sm:col-span-2 sm:mt-0">
        @if ($resource->schedule)
          <a class="text-primary" target="_blank"
             href="https://crontab.guru/#{{ str_replace(' ', '_', $resource->schedule) }}">
            {{ $resource->schedule }}
          </a>
        @endif

      </dd>
    </div>
    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
      <dt class="text-sm font-medium text-norma-gray-500">{{ __('arachno.crawler.schedule_new_works') }}</dt>
      <dd class="mt-1 text-sm text-norma-gray-900 sm:col-span-2 sm:mt-0">
        @if ($resource->schedule_new_works)
          <a class="text-primary" target="_blank"
             href="https://crontab.guru/#{{ str_replace(' ', '_', $resource->schedule_new_works) }}">
            {{ $resource->schedule_new_works }}
          </a>
        @endif
      </dd>
    </div>
    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
      <dt class="text-sm font-medium text-norma-gray-500">{{ __('arachno.crawler.schedule_changes') }}</dt>
      <dd class="mt-1 text-sm text-norma-gray-900 sm:col-span-2 sm:mt-0">
        @if ($resource->schedule_changes)
          <a class="text-primary" target="_blank"
             href="https://crontab.guru/#{{ str_replace(' ', '_', $resource->schedule_changes) }}">
            {{ $resource->schedule_changes }}
          </a>
        @endif
      </dd>
    </div>
    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
      <dt class="text-sm font-medium text-norma-gray-500">{{ __('arachno.crawler.schedule_updates') }}</dt>
      <dd class="mt-1 text-sm text-norma-gray-900 sm:col-span-2 sm:mt-0">
        @if ($resource->schedule_updates)
          <a class="text-primary" target="_blank"
             href="https://crontab.guru/#{{ str_replace(' ', '_', $resource->schedule_updates) }}">
            {{ $resource->schedule_updates }}
          </a>
        @endif
      </dd>
    </div>
    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
      <dt class="text-sm font-medium text-norma-gray-500">{{ __('arachno.crawler.schedule_updates') }}</dt>
      <dd class="mt-1 text-sm text-norma-gray-900 sm:col-span-2 sm:mt-0">
        @if ($resource->schedule_full_catalogue)
          <a class="text-primary" target="_blank"
             href="https://crontab.guru/#{{ str_replace(' ', '_', $resource->schedule_full_catalogue) }}">
            {{ $resource->schedule_full_catalogue }}
          </a>
        @endif
      </dd>
    </div>
    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
      <dt class="text-sm font-medium text-norma-gray-500">{{ __('arachno.crawler.class_name') }}</dt>
      <dd class="mt-1 text-sm text-norma-gray-900 sm:col-span-2 sm:mt-0">{{ $resource->class_name }}</dd>
    </div>
    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
      <dt class="text-sm font-medium text-norma-gray-500">{{ __('arachno.crawler.enabled') }}</dt>
      <dd class="mt-1 text-sm text-norma-gray-900 sm:col-span-2 sm:mt-0">
        {{ $resource->enabled ? __('interface.yes') : __('interface.no') }}</dd>
    </div>
    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
      <dt class="text-sm font-medium text-norma-gray-500">{{ __('arachno.crawler.needs_browser') }}</dt>
      <dd class="mt-1 text-sm text-norma-gray-900 sm:col-span-2 sm:mt-0">
        {{ $resource->needs_browser ? __('interface.yes') : __('interface.no') }}</dd>
    </div>

  </dl>
</div>


<div class="px-4 py-5 sm:px-6 flex justify-between">
  <h3 class="text-lg font-medium leading-6 text-norma-gray-900">{{ __('arachno.crawler.last_crawls') }} </h3>

  @can('collaborate.arachno.crawler.start-crawl')
    @if (!empty($crawlTypes))
      <x-ui.dropdown class="w-56">
        <x-slot name="trigger">
          <x-ui.button class="mt-1" theme="primary">
            {{ __('arachno.crawl.start_new_crawl') }}
          </x-ui.button>
        </x-slot>

        {{-- ACTIONS LIST --}}
        <div class="py-1 text-center" role="none">
          @foreach ($crawlTypes as $crawlType)
            <x-ui.form action="{{ route('collaborate.arachno.crawlers.start-crawl', ['crawler' => $resource->id, 'type' => $crawlType->value]) }}"
                       method="POST">
              <x-ui.button styling="flat" theme="gray" type="submit">
                {{ str_replace('_', ' ', $crawlType->name) }}
              </x-ui.button>
            </x-ui.form>
          @endforeach
        </div>
      </x-ui.dropdown>
    @endif
  @endcan
</div>

<div>

  <dl class="sm:divide-y sm:divide-norma-gray-200">
    @foreach ($resource->crawls()->withCount('urlFrontierLinks')->limit(50)->get() as $crawl)
      <a class="block hover:bg-norma-gray-100"
         href="{{ route('collaborate.arachno.crawls.url-frontier-links.index', ['crawl' => $crawl->id]) }}">
        <div class="py-2 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">

          <dt class="text-sm font-medium text-norma-gray-500 tippy"
              data-tippy-content="{{ __('interface.date_created') . ': ' . $crawl->created_at . '  UTC' }}">
            {{ __('arachno.crawl.crawl_types.crawl_type_' . $crawl->crawl_type) }}
          </dt>
          <dd class="mt-1 text-sm text-norma-gray-900 sm:col-span-2 sm:mt-0">
            {{ $crawl->started_at ? __('arachno.crawl.started_at') . ': ' . $crawl->started_at . ' UTC' : __('arachno.crawl.not_started') }}

            @if ($crawl->started_at)
              <span class="text-norma-gray-500 ml-10 text-xs">
                ({{ trans_choice('arachno.url_frontier_link.url_link_count', $crawl->url_frontier_links_count, ['value' => $crawl->url_frontier_links_count]) }})
              </span>
            @endif
          </dd>

        </div>
      </a>
    @endforeach

  </dl>

</div>
