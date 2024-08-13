@props(['closable', 'rounded', 'small' => false])
{{-- Don't remove - Tailwind JIT Detect:
  pr-1.5
  pr-1
  pr-2.5
  pl-1.5
  pl-2.5 --}}

@php
$pr = isset($closable) && $closable ? ($small ? '1.5' : '1') : ($small ? '1.5' : '2.5');
@endphp
<span
      {{ $attributes->merge(['class' => 'inline-flex rounded-' . ($rounded ?? 'full') . ' items-center py-0.5 pl-' . ($small ? '1.5' : '2.5') . ' pr-' . $pr . ' text-' . ($small ? 'xs' : 'sm') . ' font-medium bg-secondary text-white']) }}>
  {{ $slot }}
  @if (isset($closable) && $closable)
    <button @click="$dispatch('closed')" type="button"
            class="shrink-0 ml-0.5 h-4 w-4 rounded-full inline-flex items-center justify-center text-white hover:bg-secondary-darker focus:outline-none focus:bg-secondary-darker focus:text-white">
      <svg class="h-2 w-2" stroke="currentColor" fill="none" viewBox="0 0 8 8">
        <path stroke-linecap="round" stroke-width="1.5" d="M1 1l6 6m0-6L1 7" />
      </svg>
    </button>
  @endif
</span>
