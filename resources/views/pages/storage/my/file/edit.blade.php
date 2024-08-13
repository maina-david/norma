<x-layouts.app>
  <x-slot name="header">
    <div class="flex items-center">
      <a href="{{ route('my.drives.files.show', ['file' => $file->id]) }}" class="text-primary rounded-full border border-primary p-2 flex items-center justify-center">
        <x-ui.icon name="chevron-left" size="2" />
      </a>

      <x-ui.file-icon :mime-type="$file->mime_type" class="mx-4" size="8" />

      <div>{{ $file->title }}</div>
    </div>
  </x-slot>

  <x-ui.form class="w-1/2" :action="route('my.drives.files.update', ['file' => $file->id])" method="put">
    <x-ui.input :value="old('title', $file->title ?? '')" name="title" :label="__('interface.name')" />

    <x-ui.input type="date" :value="old('expires_at', $file->expires_at ?? '')" name="expires_at"
                :label="__('timestamps.expires_at')" />

    <x-ontology.user-tag-selector :selected="$file->userTags" />

    <x-ui.button class="mt-10" type="submit" theme="primary">
      {{ __('actions.save') }}
    </x-ui.button>
  </x-ui.form>


</x-layouts.app>
