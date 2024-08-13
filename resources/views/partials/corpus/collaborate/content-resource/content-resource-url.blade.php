<div>

  <x-ui.alert-box class="my-7">
    {{ __('corpus.content-resource.file_upload_url_info') }}
  </x-ui.alert-box>
  <div class="flex pb-3 items-center">
    <x-ui.button styling="outline" theme="gray" class="mr-3 tippy"
                 data-tippy-content="{{ __('actions.copy_to_clipboard') }}"
                 @click="navigator.clipboard.writeText('{{ $url }}');window.toast.success({ message: '{{ __('actions.copied') }}' })">
      <x-ui.icon name="copy" />
    </x-ui.button>
    <a class="text-primary" href="{{ $url }}" target="_blank">{{ $url }}</a>
  </div>

</div>
