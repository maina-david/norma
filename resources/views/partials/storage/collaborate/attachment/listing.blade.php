<div>
  @can('collaborate.storage.attachment.create')
    <x-storage.collaborate.uploader :route="route('collaborate.task.attachments.store', ['task' => $task->id])" />
  @endcan

  <div class="mt-4 space-y-2">
    @foreach($attachments as $attachment)
      <div class="relative group">
        <a target="_blank" class="flex items-center rounded px-2 py-1 block border border-libryo-gray-200 hover:border-primary text-primary overflow-x-hidden" href="{{ route('collaborate.task.attachments.show', ['attachment' => $attachment->id, 'task' => $task->id]) }}">
          <x-ui.file-icon :mime-type="$attachment->mime_type" />
          <span class="whitespace-nowrap ml-1 flex-grow overflow-x-hidden text-ellipsis">{{ $attachment->name }}</span>
        </a>

      @can('delete', $attachment)
        <div class="bg-white absolute right-0 top-0 m-0.5 hidden group-hover:block">
          <x-ui.delete-button size="sm" :route="route('collaborate.task.attachments.destroy', ['task' => $task->id, 'attachment' => $attachment->id])">
            <x-ui.icon name="trash-alt" />
          </x-ui.delete-button>
        </div>
      @endcan

      </div>
    @endforeach
  </div>
</div>
