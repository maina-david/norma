<x-ui.input required
            name="title"
            label="{{ __('storage.folder.name') }}"
            :value="old('title', $folder->title ?? '')" />

<input type="hidden" value="{{ $folderType }}" name="folder_type" />
