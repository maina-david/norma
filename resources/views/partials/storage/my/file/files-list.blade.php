  @foreach ($files as $file)
    <x-storage.file-panel :file="$file"
                          :allow-delete="!$file->isGlobal() && !$file->isSystem() && (($file->can_manage_org || $file->is_author) && isset($file->folder) && !$file->folder->for_attachments)"
                          color="gray-500"
                          :files-related-id="$filesRelatedId ?? null"
                          :files-relation="$filesRelation ?? null"
                          :route="route('my.drives.files.show', ['file' => $file->id])"
                          :show-details="isset($filesRelation) && $filesRelation !== 'comment'"
                          :bulk="$bulk ?? false"
    />
  @endforeach
  <div class="mt-5">
    {{ $files->links() }}
  </div>
