<div class="border-b border-norma-gray-200 pb-4">
  <x-storage.file-panel
      :file="$file"
      allow-delete
      color="gray-500"
      route="#"
      :delete-route="route('my.settings.files.global.destroy', $file->id)"
      :show-details="isset($filesRelation) && $filesRelation !== 'comment'"
      style="border:none !important;padding: 0;"
  >
    <div class="text-xs">
      <div class="flex flex-wrap">
        @foreach ($file->legalDomains as $domain)
          <div class="mr-2 rounded-full px-2 bg-norma-gray-200">{{ $domain->title }}</div>
        @endforeach
      </div>
      <div class="flex flex-wrap mt-1">
        @foreach ($file->locations as $location)
          <div class="mr-2 rounded-full px-2 bg-red-200">{{ $location->title }}</div>
        @endforeach
      </div>
    </div>
  </x-storage.file-panel>
</div>
