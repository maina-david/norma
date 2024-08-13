<x-dynamic-component :component="$appLayout" :fluid="$fluid ?? false">

  @if($actions ?? false)
    <x-slot name="actions">
      @include($actions)
    </x-slot>
  @endif

  <div class="px-4 py-4 sm:px-0 flex justify-center">
    <x-slot name="header">
      @if ($application->value === App\Enums\Application\ApplicationType::collaborate()->value)
        <x-ui.breadcrumb class="-my-2">

          @if (Route::has($baseRoute . '.index') && Auth::user()->can($permission . '.viewAny'))
            <x-ui.breadcrumb-item :route="route($baseRoute . '.index', $baseRouteParams ?? [])">{{ $title ?? __("{$langFile}.index_title", $langParams ?? []) }}
            </x-ui.breadcrumb-item>
          @else
            <x-ui.breadcrumb-item>{{ $title ?? __("{$langFile}.index_title", $langParams ?? []) }}</x-ui.breadcrumb-item>
          @endif

          <x-ui.breadcrumb-item>{{ __("{$langFile}.edit_title", $langParams ?? []) }}</x-ui.breadcrumb-item>
        </x-ui.breadcrumb>
      @else
        <span>{{ __("{$langFile}.edit_title", $langParams ?? []) }}</span>
      @endif

      @isset($bottomHeader)
        @include($bottomHeader)
      @endisset
    </x-slot>

    <x-ui.card :class="$formWidth ?? 'max-w-lg'">

      <x-ui.form :enctype="$enctype ?? null" method="put" :action="route($baseRoute . '.update', array_merge((array) $resource->id, $baseRouteParams ?? []))">

        @include($form)

      </x-ui.form>

    </x-ui.card>

  </div>


</x-dynamic-component>
