<div class="flex items-center justify-center">
  @if($row->status)
    <x-ui.icon class="text-primary" name="circle-check" />
  @else
    <x-ui.icon class="text-secondary" name="circle-xmark" />
  @endif
</div>
