@props(['route' => '', 'searchStr' => '', 'searchForText' => __('interface.search'), 'frameTarget' => '_top'])
<div {{ $attributes }}>
  <div class="text-xl mt-10 mb-3">
    <x-ui.icon name="search" class="mr-3" />
    <span>{{ __('interface.word_search') }}</span>
  </div>
  <div class="italic text-sm">
    <span>{{ $searchForText ?? '' }}: </span>
    @if ($route)
      <a data-turbo-frame="{{ $frameTarget }}"
         href="{{ $route }}"
         class="text-primary">{{ $searchStr }}
      </a>
    @else
      <span @click="$dispatch('filtered', { value: '{{ $searchStr }}', filter: 'search' });"
            class="text-primary cursor-pointer">
        {{ $searchStr }}
      </span>
    @endif

  </div>
</div>
