<table style="font-size: 15px; line-height: 26px; color: #1a2434" class="task-table" width="100%" cellpadding="5" cellspacing="3">
<tr >
<th colspan="2">
{{__('tasks.task_details')}}
</th>
</tr>

@if(!empty($assignedTo))
<tr>
<td><b>{{__('tasks.assigned_to')}}</b></td>
<td>
{{$assignedTo}}
</td>
</tr>
@endif

<tr>
<td width="150"><b>{{__('tasks.task_type')}}</b></td>
<td>
{{__('tasks.task.types.' . $task->taskable_type)}}
</td>
</tr>
@if(!empty($item))
<tr>
<td><b>{{__('tasks.task_item')}}</b></td>
<td>
@if (empty($url))
{{ $item }}
@else
<a href="{{ $url }}">{{ $item }}</a>
@endif
</td>
</tr>
@endif
@if(!empty($due_on))
<tr>
<td><b>{{__('tasks.due_on')}}</b></td>
<td>
{{$due_on}}
</td>
</tr>
@endif
@if(!empty($task) && !empty($task->place))
<tr>
<td><b>{{__('tasks.libryo_stream')}}</b></td>
<td>
{{$task->place->title}}
</td>
</tr>
@endif
@if(!empty($task))
<tr>
<td><b>{{__('tasks.current_status')}}</b></td>
<td>
{{__('tasks.task.status.' . $task->task_status)}}
</td>
</tr>
@endif
@if($task->priority !== null)
<tr>
<td><b>{{__('tasks.priority')}}</b></td>
<td>
{{__('tasks.task.priority.' . $task->priority)}}
</td>
</tr>
@endif
@if(!empty($changedBy))
<tr>
<td><b>{{__('tasks.changed_by')}}</b></td>
<td>
{{$changedBy}}
</td>
</tr>
@endif
@if(!empty($completedBy))
<tr>
<td><b>{{__('tasks.completed_by')}}</b></td>
<td>
{{$completedBy}}
</td>
</tr>
@endif
@if($task->taskProject)
<tr>
<td><b>{{__('tasks.project')}}</b></td>
<td>
{{$task->taskProject->title}}
</td>
</tr>
@endif
</table>
