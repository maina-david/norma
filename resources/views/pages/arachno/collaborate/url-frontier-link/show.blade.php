<div class="border-t border-norma-gray-200 px-4 py-5 sm:p-0">
  <dl class="sm:divide-y sm:divide-norma-gray-200">
    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
      <dt class="text-sm font-medium text-norma-gray-500">{{ __('arachno.url_frontier_link.url') }}</dt>
      <dd class="mt-1 text-sm text-norma-gray-900 sm:col-span-2 sm:mt-0">{{ $resource->url }}</dd>
    </div>
    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
      <dt class="text-sm font-medium text-norma-gray-500">{{ __('arachno.url_frontier_link.date_crawled') }}</dt>
      <dd class="mt-1 text-sm text-norma-gray-900 sm:col-span-2 sm:mt-0">{{ $resource->crawled_at }}</dd>
    </div>
    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
      <dt class="text-sm font-medium text-norma-gray-500">{{ __('arachno.url_frontier_link.for_doc') }}</dt>
      <dd class="mt-1 text-sm text-norma-gray-900 sm:col-span-2 sm:mt-0">
        @if ($resource->doc)
          <a class="text-primary" href="{{ route('collaborate.corpus.docs.show', ['doc' => $resource->doc_id]) }}">
            {{ $resource->doc?->title }}
          </a>
        @endif

      </dd>
    </div>
  </dl>
</div>


<div class="px-4 py-5 sm:px-6">
  <h3 class="text-lg font-medium leading-6 text-norma-gray-900">{{ __('arachno.url_frontier_link.crawler_info') }} </h3>
</div>

<div class="border-t border-norma-gray-200 px-4 py-5 sm:p-0">
  <dl class="sm:divide-y sm:divide-norma-gray-200">
    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
      <dt class="text-sm font-medium text-norma-gray-500">{{ __('arachno.url_frontier_link.crawl_id') }}</dt>
      <dd class="mt-1 text-sm text-norma-gray-900 sm:col-span-2 sm:mt-0">{{ $resource->crawl_id }}</dd>
    </div>
    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
      <dt class="text-sm font-medium text-norma-gray-500">{{ __('arachno.url_frontier_link.crawler') }}</dt>
      <dd class="mt-1 text-sm text-norma-gray-900 sm:col-span-2 sm:mt-0">
        <div>{{ $resource->crawl?->crawler?->title }}</div>
        <div class="text-norma-gray-500">({{ $resource->crawl?->crawler?->slug }})</div>
      </dd>
    </div>
    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
      <dt class="text-sm font-medium text-norma-gray-500">{{ __('arachno.url_frontier_link.source') }}</dt>
      <dd class="mt-1 text-sm text-norma-gray-900 sm:col-span-2 sm:mt-0">
        @if ($resource->crawl?->crawler?->source)
          <a class="text-primary"
             href="{{ route('collaborate.sources.show', ['source' => $resource->crawl?->crawler?->source_id]) }}">
            {{ $resource->crawl?->crawler?->source?->title }}
          </a>
        @endif
      </dd>
    </div>

  </dl>
</div>


<div class="px-4 py-5 sm:px-6">
  <h3 class="text-lg font-medium leading-6 text-norma-gray-900">{{ __('arachno.url_frontier_link.crawler_meta') }} </h3>
</div>


<div class="px-4 py-5 sm:px-6">
  <h3 class="text-lg font-medium leading-6 text-norma-gray-900">{{ __('arachno.url_frontier_link.link_info') }} </h3>
</div>

<div class="border-t border-norma-gray-200 px-4 py-5 sm:p-0">
  <dl class="sm:divide-y sm:divide-norma-gray-200">
    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
      <dt class="text-sm font-medium text-norma-gray-500">{{ __('arachno.url_frontier_link.referer') }}</dt>
      <dd class="mt-1 text-sm text-norma-gray-900 sm:col-span-2 sm:mt-0">
        @if ($resource->refererLink)
          <a class="text-primary"
             href="{{ route('collaborate.url-frontier-links.show', ['url_frontier_link' => $resource->referer_id]) }}">
            {{ $resource->refererLink?->url }}
          </a>
        @endif
      </dd>
    </div>
    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
      <dt class="text-sm font-medium text-norma-gray-500">{{ __('arachno.url_frontier_link.anchor_text') }}</dt>
      <dd class="mt-1 text-sm text-norma-gray-900 sm:col-span-2 sm:mt-0">{{ $resource->anchor_text }}</dd>
    </div>
    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
      <dt class="text-sm font-medium text-norma-gray-500">{{ __('arachno.url_frontier_link.referer_text') }}</dt>
      <dd class="mt-1 text-sm text-norma-gray-900 sm:col-span-2 sm:mt-0">{{ $resource->referer }}</dd>
    </div>
    <div class="py-4 sm:grid sm:grid-cols-3 sm:gap-4 sm:py-5 sm:px-6">
      <dt class="text-sm font-medium text-norma-gray-500">{{ __('arachno.url_frontier_link.needs_browser') }}</dt>
      <dd class="mt-1 text-sm text-norma-gray-900 sm:col-span-2 sm:mt-0">
        {{ $resource->needs_browser ? __('interface.yes') : __('interface.no') }}</dd>
    </div>

  </dl>
</div>

<div class="px-4 py-5 sm:px-6">
  <h3 class="text-lg font-medium leading-6 text-norma-gray-900">{{ __('arachno.url_frontier_link.log') }} </h3>
</div>

<div>
  @if ($resource->frontier_log)
    <pre class="p-5  bg-norma-gray-700 text-norma-gray-100 text-sm leading-loose whitespace-normal w-full">{{ $resource->frontier_log }}</pre>
  @endif
</div>
