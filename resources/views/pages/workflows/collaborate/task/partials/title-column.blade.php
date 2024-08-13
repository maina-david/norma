<a class="text-primary" href="{{ route('collaborate.tasks.show', ['task' => $row->id]) }}">{{ $row->title }}</a>

<div class="text-gray-400 text-sm">
    {{ $row->taskType->name }}
</div>
