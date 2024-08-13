@props(['label', 'filters' => [], 'name' => 'task_type_id', 'required' => false, 'value' => null, 'multiple' => false, 'permissioned' => false])

@php
$options = \App\Models\Workflows\TaskType::orderBy('name')
  ->when($filters, fn ($builder) => $builder->where($filters))
  ->get(['name', 'id']);

if ($permissioned) {
    $user = \Illuminate\Support\Facades\Auth::user();
    $options = $options->filter(fn ($item) => $user->can($item->permission(true)));
}

$options = ['' => '-'] + $options->pluck('name', 'id')->toArray();
@endphp

<x-ui.input
  :multiple="$multiple"
  :name="$name"
  :required="$required"
  label="{{ $label ?? __('workflows.task_type.index_title') }}"
  :value="$value ?? ''"
  type="select"
  :options="$options"
  @tom-changed="$dispatch('changed', $event.detail)"
  {{ $attributes }}
/>
