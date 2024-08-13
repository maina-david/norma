<div>
  <div>
    <a
      class="text-primary tippy flex flex-col"
      data-turbo-frame="_top"
      href="{{ $link }}"
      data-tippy-content="{{ __('storage.file.activate_stream_and_go') }}"
    >
      <span>{{ $file->title }}</span>
      @if(!empty($file->description ?? ''))
        <span class="text-libryo-gray-500 text-xs">{{ $file->description }}</span>
      @endif
    </a>
  </div>
  @if ($file->isTypeLibryo())
    <div class="text-libryo-gray-500 ">{{ $file->libryo?->title }}</div>
  @endif

</div>
