<div class="text-libryo-gray-600 flex flex-col items-center">
  @if (isset($answeredAt) && $answeredAt)
    <div class="whitespace-nowrap">
      <x-ui.timestamp :timestamp="$answeredAt" />
    </div>
  @endif

  <div class="italic text-xs">{{ $user->fullName ?? '-' }}</div>
</div>
