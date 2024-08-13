  @props(['title', 'icon', 'filterByText'])
  <div class="mt-10">
    <div class="text-xl mb-4">
      <x-ui.icon :name="$icon" class="mr-3" />
      <span>{{ $title }}</span>
    </div>
    <div class="italic text-sm mb-4">
      <x-ui.icon name="filter" size="3" class="mr-1" />
      <span>{{ $filterByText }}: </span>
    </div>

    {{ $slot }}
  </div>
