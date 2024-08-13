@props(['home' => '/'])
<nav {{ $attributes->merge(['class' => 'flex text-libryo-gray-600 breadcrumb']) }} aria-label="Breadcrumb">
  <ol role="list" class="bg-white flex space-x-4">
    <li class="flex">
      <div class="flex items-center">
        <a href="{{ $home }}" class="flex items-center text-primary font-medium hover:text-primary">
          <x-ui.icon name="home" size="6" />
          <span class="sr-only">{{ __('nav.home') }}</span>
        </a>
      </div>
    </li>

    {{ $slot }}
  </ol>
</nav>
