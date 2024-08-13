@props(['work'])
<div class="inline-block mr-2 rounded bg-libryo-gray-200 text-libryo-gray-700 px-1.5 py-0.5 text-xs">
  <div class="flex flex-row items-center">
    <x-ui.icon name="file-alt" size="3" class="mr-1" />
    {{ __('corpus.work.types.' . $work->work_type) }}
  </div>
</div>
