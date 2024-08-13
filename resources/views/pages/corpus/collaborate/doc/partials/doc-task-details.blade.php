@if($task && $task->id != '0')
  <div class="text-sm pb-4 w-full overflow-hidden">
    <div class="flex flex-col md:flex-row space-x-2 whitespace-nowrap text-ellipsis w-4/5 ">
      <div>{{ $task->document->title }}</div>
    </div>
  </div>
@endif



