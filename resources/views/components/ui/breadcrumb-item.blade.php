@props(['route'])
<li class="flex">
  <div class="flex items-center">
    <svg class="shrink-0 w-6 h-full text-norma-gray-300" viewBox="0 0 24 44" preserveAspectRatio="none"
         fill="currentColor" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
      <path d="M.293 0l22 22-22 22h1.414l22-22-22-22H.293z" />
    </svg>

    @isset($route)
      <a
         href="{{ $route }}"
         class="ml-4 text-base text-primary font-medium py-4">{{ $slot }}</a>
    @else
      <span class="ml-4 text-base font-medium py-4">{{ $slot }}</span>
    @endisset
  </div>
</li>
