@php
$upload = $upload ?? false;
@endphp

@if ($percentage < 100)
  <div class="mt-10">
    <x-ui.empty-state-icon icon="cogs" :title="($upload ? __('actions.processing_import') : __('actions.generating_export')) . '...'" />
  </div>

  <div class="p-10">
    <x-ui.form
      method="post"
      x-init="setTimeout(function() { $el.requestSubmit(); }, 1000)"
      :action="route('my.job-statuses.show.by.job', ['jobId' => $jobId, 'upload' => (int) $upload, 'redirect' => $redirect])"
    >
      @isset($target)
        <input type="hidden" name="turbo-target" value="{{ $target }}">
      @endisset
    </x-ui.form>

    <div class="relative pt-1">
      <div class="overflow-hidden h-2 text-xs flex rounded bg-norma-gray-200">
        <div
           x-ref="progressBar"
           style="width: {{ $percentage }}%;"
           class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-primary"
        >
        </div>
      </div>
    </div>
  </div>
@else
  <div x-init="{{ $redirect ? 'window.location = \'' . $redirect . '\';' : '' }}" class="mt-10">
    @if($upload)
      <x-ui.empty-state-icon icon="check-circle" title="{{ __('interface.your_upload_completed') }}" />
    @else
      <x-ui.empty-state-icon icon="check-circle" title="{{ __('interface.your_download_should_appear') }}" />
    @endif
  </div>
@endif
