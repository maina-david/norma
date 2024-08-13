@props(['task', 'singleMode' => true])
<a {!! isset($newTab) && $newTab ? 'target="_blank" data-turbo="false"' : '' !!}
   {{ $attributes->merge(['class' => 'text-primary hover:text-primary-darker']) }}
   data-turbo-frame="_top"
   href="{{ route('my.tasks.tasks.show', ['task' => $task->hash_id]) }} "
>
   <span class="font-semibold">{{ $task->title }}</span>
   @if(!$singleMode)
      <span class="ml-1 text-libryo-gray-400">/ {{ $task->libryo->title }}</span>
   @endif
</a>
