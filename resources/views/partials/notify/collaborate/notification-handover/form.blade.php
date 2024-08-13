@php
use App\Models\Workflows\Board;
$boards = Board::orderBy('title')->pluck('title', 'id')->all();
$boards[''] = '-';
@endphp

<x-ui.input
  :value="old('text', $resource->text ?? '')"
  name="text"
  label="{{ __('corpus.reference.text') }}"
/>

<x-ui.input
  :value="old('board_id', $resource->board_id ?? '')"
  name="board_id"
  type="select"
  label="{{ __('workflows.monitoring_task_config.board') }}"
  :options="$boards"
/>

<x-slot name="footer">
  <div></div>
  <div>
    <x-ui.back-button :fallback="route('collaborate.notification-handovers.index')" />
    <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
  </div>
</x-slot>
