@props(['node', 'collapsable' => false, 'bold' => false])
@php
/** @var \App\Support\Actions\ActionAreaTreeNode $node */

$headerAction = $collapsable ? ' @click="open = !open"' : '';
$classes = $collapsable ? [] : '';
$shouldBold = $collapsable || ($bold && !empty($node->children()));
$status = $node->status();
@endphp
<div class="w-full" x-data="{ open: {{ json_encode(!$collapsable) }} }">

  <div
    {!! $headerAction !!}
    class="px-6 py-4 flex items-center {{ $collapsable ? 'cursor-pointer' : '' }} {{ $shouldBold ? 'font-semibold' : '' }}"
  >
    <div class="flex-grow flex items-center">
      @if($collapsable)
        <div class="w-10 flex-shrink-0">
          @if($node->icon())
            <x-ui.icon class="text-primary" :name="$node->icon()" />
          @elseif($node->flag())
            <x-ui.country-flag class="h-6 2-6 rounded-full overflow-hidden" :countryCode="$node->flag()" />
          @endif
        </div>
      @endif

      <div>
        @if(!$collapsable && $node->href())
          <a href="{{ $node->href() }}" target="_top" data-turbo="false">
            {{ $node->label() }}
          </a>
        @else
          {{ $node->label() }}
        @endif
      </div>
    </div>
    <div class="flex-shrink-0 w-40">
      <x-ui.grouped-user-avatars :users="$node->assignees()" />
    </div>
    <div class="flex-shrink-0 w-40 text-center">
      @if(!$node->isReferenceCountHidden())
        {{ $node->referencesCount() }}
      @endif
    </div>
    <div class="flex-shrink-0 w-40 text-center">{{ $node->taskCount() }}</div>
    <div class="flex-shrink-0 w-40 text-center {{ $status?->textColour() ?? '' }}">{{ $status?->label() ?? '-' }}</div>
    <div class="w-10 text-primary flex-shrink-0">
      @if($collapsable)
        <x-ui.icon style="display:none" x-show="!open" name="angle-down" />
        <x-ui.icon style="display:none" x-show="open" name="angle-up" />
      @endif
    </div>
  </div>

  <div class="bg-white" x-show="open" style="display: none">
    @if($collapsable)
      <div class="flex items-center w-full font-semibold">
        <div class="flex-grow">
          <div class="font-semibold px-6 py-4 ">
            @if($node->href())
              <a href="{{ $node->href() }}">
                {{ $node->label() }}
              </a>
            @else
              {{ $node->label() }}
            @endif
          </div>
        </div>
        <div class="flex-shrink-0 w-40"></div>
        <div class="flex-shrink-0 w-40 text-center"></div>
        <div class="flex-shrink-0 w-40 text-center"></div>
        <div class="flex-shrink-0 w-40 text-center"></div>
        <div class="w-10"></div>
      </div>
    @endif


    <div class="pl-4">
      @foreach($node->children() as $child)
        <x-actions.action-area-tree-node :node="$child" :bold="$collapsable" />
      @endforeach
    </div>
  </div>

</div>
