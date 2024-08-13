<x-dynamic-component :component="$appLayout" :fluid="$fluid ?? false">
  <x-slot name="header">
    @if ($application->value === App\Enums\Application\ApplicationType::collaborate()->value)
      <x-ui.breadcrumb class="-my-2">

        @if (Route::has($baseRoute . '.index') && Auth::user()->can($permission . '.viewAny'))
          <x-ui.breadcrumb-item :route="route($baseRoute . '.index', $baseRouteParams ?? [])">{{ $title ?? __("{$langFile}.index_title") }}
          </x-ui.breadcrumb-item>
        @else
          <x-ui.breadcrumb-item>{{ $title ?? __("{$langFile}.index_title") }}</x-ui.breadcrumb-item>
        @endif

        <x-ui.breadcrumb-item>{{ __("{$langFile}.create_title") }}</x-ui.breadcrumb-item>
      </x-ui.breadcrumb>
    @else
      <span>{{ __("{$langFile}.create_title") }}</span>
    @endif
  </x-slot>

  <div class="px-4 py-4 sm:px-0 flex justify-center">

    <x-ui.card :class="$formWidth ?? 'max-w-lg'">

      <x-ui.form :enctype="$enctype ?? null" method="post" :action="route($baseRoute . '.store', $baseRouteParams ?? [])">

        @include($form)

      </x-ui.form>

    </x-ui.card>


  </div>

</x-dynamic-component>
