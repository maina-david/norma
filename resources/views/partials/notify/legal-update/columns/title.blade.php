<x-ui.link :href="route('my.settings.legal-updates.show', ['update' => $row->id])">
  @if($row->libraries_count > 0 || $row->normas_count > 0)
    <span class="text-green-600">{{ $row->title }}</span>
  @else
    <span class="text-secondary">{{ $row->title }}</span>
  @endif
</x-ui.link>
