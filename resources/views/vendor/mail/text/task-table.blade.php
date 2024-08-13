@component('mail::table')
| {{__('tasks.task_details')}}       |          |
|:------------- |-------------:| 
@if(!empty($assignedTo))
| {{__('tasks.assigned_to')}}      | {{$assignedTo}}      | 
@endif
| {{__('tasks.task_type')}}      | {{__('tasks.task.types.' . $task->taskable_type)}} |
@if(!empty($item))
| {{__('tasks.task_item')}}      | {{ $item }}    | 
@endif
@if(!empty($due_on))
| {{__('tasks.due_on')}}      | {{ $due_on }}    | 
@endif
@if(!empty($task) && !empty($task->place))
| {{__('tasks.libryo_stream')}}      | {{$task->libryo->title}}    | 
@endif
@if(!empty($task))
| {{__('tasks.current_status')}}      | {{__('tasks.task.status.' . $task->task_status)}}    | 
@endif
@if($task->priority !== null)
| {{__('tasks.priority')}}      | {{__('tasks.task.priority.' . $task->priority)}}    | 
@endif
@if(!empty($changedBy))
| {{__('tasks.changed_by')}}      | {{$changedBy}}    | 
@endif
@if(!empty($completedBy))
| {{__('tasks.completed_by')}}      | {{$completedBy}}    | 
@endif
@if($task->project)
| {{__('tasks.project')}}      | {{$task->project->title}}    | 
@endif
@endcomponent

{{ $slot }}