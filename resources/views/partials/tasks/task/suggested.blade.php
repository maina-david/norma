@php use App\Models\Corpus\Reference; @endphp
<div class="border-b border-norma-gray-200 mb-4 pb-4 px-4">
  <div class="font-semibold text-xl flex items-center">
    <span>{{ __('tasks.suggested_tasks') }}</span>
    <span class="ml-2 rounded-full bg-norma-gray-600 text-xs px-2 py-1 text-white tippy" data-tippy-content="{{ __('interface.alpha_tooltip') }}">
      {{ __('interface.alpha') }}
    </span>
  </div>
  <div class="text-sm my-4 text-norma-gray-700">{{ __('tasks.suggested_tasks_description') }}</div>
  <div class="text-sm text-semibold text-norma-gray-800">{{ __('tasks.suggested_tasks_are_editable') }}</div>
</div>

<x-ui.form :action="route('my.tasks.tasks.bulk.store')" method="post">
  <input type="hidden" name="norma_id" value="{{ $norma->id }}" />
  <input type="hidden" name="taskable_type" value="{{ $taskableType }}" />
  <input type="hidden" name="taskable_id" value="{{ $taskableId }}" />

  <div class="space-y-4 px-4">
    @foreach($suggestions as $index => $item)
      <div><x-ui.input id="task_{{ $index }}" type="checkbox" label="{{ $item }}" name="tasks[]" checkbox-value="{{ $item }}" checked /></div>
    @endforeach
  </div>

  @if(!isset($task) && $taskableType === (new Reference())->getMorphClass())
    <div class="mt-4 px-4 border-t border-norma-gray-200 pt-4">
      <x-ui.input type="checkbox" :value="old('copy')" name="copy" label="{{ __('tasks.create_copies') }}"/>
    </div>
  @endif

  <div class="flex justify-end items-center my-4">
    <x-ui.button type="submit" theme="primary">{{ __('tasks.add_tasks') }}</x-ui.button>
  </div>
</x-ui.form>
