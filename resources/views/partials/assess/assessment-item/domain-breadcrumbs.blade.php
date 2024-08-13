<div class="text-libryo-gray-400 text-xs italic">
  @if ($assessmentItem?->legalDomain?->topParent)
    <span>{{ $assessmentItem->legalDomain->topParent->title }}</span>
    <span> ... </span>
  @endif

  @if ($assessmentItem->legalDomain)
    <span class="font-bold">{{ $assessmentItem->legalDomain->title }}</span>
  @endif

</div>
