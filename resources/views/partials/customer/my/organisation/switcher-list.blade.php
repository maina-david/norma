<div class="overflow-y-scroll h-full max-h-screen">
  @foreach ($listItems as $id => $item)
    <x-ui.form method="post" action="{{ route('my.settings.organisations.activate', ['organisation' => $id]) }}">
      <button class="text-primary my-3 block text-left" role="menuitem" tabindex="-1">
        {{ $item }}
      </button>
    </x-ui.form>
  @endforeach
</div>
