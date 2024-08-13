<div>
  @foreach ($items as $item)
    <div>
      <a href="{{ route($item['route']) }}"
         class="p-5 flex flex-row my-1 items-center hover:text-primary {{ request()->routeIs($item['route']) ? 'text-primary border-r border-primary' : '' }}">
        <x-ui.icon :name="$item['icon']" class="mx-5 text-lg" />
        <span>
          {{ $item['label'] }}
        </span>
      </a>
    </div>
  @endforeach
</div>
