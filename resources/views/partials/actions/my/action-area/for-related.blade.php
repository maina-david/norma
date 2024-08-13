<div>
  @if ($actions->isNotEmpty())
    <div class="bg-white border border-gray-100 shadow">
      @foreach($actions as $action)
        <div class="p-4 {{ $loop->even ? 'bg-gray-100' : '' }}">
          {{ $action->title }}
        </div>
      @endforeach
    </div>
  @else
    <x-ui.empty-state-icon icon="clipboard-list-check" :title="__('actions.action_area.no_action_areas')" />
  @endif
</div>
