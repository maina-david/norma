<div class="flex flex-col justify-center mr-3 whitespace-nowrap">
  <div class="sub-row text-norma-gray-700">

    @isset($row->payload['source_id'])
      <div>{{ __('system.processing_job.source_id') }}: {{ $row->payload['source_id'] }}</div>
    @endisset

    @isset($row->payload['source_unique_id'])
      <div>{{ __('system.processing_job.source_unique_id') }}: {{ $row->payload['source_unique_id'] }}</div>
    @endisset

    @isset($row->payload['spider_slug'])
      <div>{{ __('system.processing_job.spider_slug') }}: {{ $row->payload['spider_slug'] }}</div>
    @endisset

    @isset($row->payload['start_url'])
      <div class="max-w-sm text-ellipsis overflow-hidden">
        {{ __('system.processing_job.start_url') }}:
        <a class="text-primary" href="{{ $row->payload['start_url'] }}"
           target="_blank">{{ $row->payload['start_url'] }}</a>
      </div>
    @endisset

  </div>
</div>
