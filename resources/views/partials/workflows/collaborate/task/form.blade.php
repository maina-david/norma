@php
use App\Models\Auth\User;
$users = collect(['user_id', 'manager_id'])->map(fn ($field) => $resource->{$field} ?? null)->filter();

if ($users->isNotEmpty()) {
    $users = User::find($users->values()->toArray(), ['id', 'fname', 'sname']);
    $users = $users->keyBy('id');
}
@endphp

<div>
  @include('partials.workflows.collaborate.task.defaults', ['defaults' => $resource, 'forTask' => true, 'group' => $resource->group])
</div>
