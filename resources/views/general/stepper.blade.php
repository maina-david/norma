<div class="flex justify-evenly">
  @foreach($order as $key => $value)
    <x-ui.step :active="$key + 1 === $active" number="{{ $key + 1 }}" name="{{ $value['title'] }}" />
  @endforeach
</div>


<div class="mt-8">
  <x-ui.form action="{{ route($route, [...($routeParams ?? []), 'stage' => $active]) }}" method="POST" enctype="multipart/form-data">

    @include($order[$active - 1]['partial'], ['stepMeta' => $order[$active - 1]])

    <div class="flex justify-between mt-6">
      <div>
        @if($active > 1)
          <a
            class="inline-flex items-center px-4 py-2 border-transparent rounded-md font-semibold text-xs uppercase transition ease-in-out duration-150 tracking-widest bg-dark border text-white hover:bg-dark active:bg-dark focus:outline-none focus:border-dark focus:ring ring-dark"
            href="{{ route($route, [...($routeParams ?? []), 'stage' => $active - 1]) }}"
          >
            {{ __('actions.previous') }}
          </a>
        @endif
      </div>

      <div>
        <x-ui.button type="submit" theme="primary">
          {{ __($isLast ? 'actions.finish' : 'actions.next') }}
        </x-ui.button>
      </div>
    </div>


  </x-ui.form>
</div>




