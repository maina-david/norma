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
            <x-ui.breadcrumb-item :route="route($baseRoute . '.index', $baseRouteParams ?? [])">{{ $title ?? __("{$langFile}.index_title") }}
            </x-ui.breadcrumb-item>
          @else
            <x-ui.breadcrumb-item>{{ $title ?? __("{$langFile}.index_title") }}</x-ui.breadcrumb-item>
          @endif

          <x-ui.breadcrumb-item>{{ isset($showTitle) ? $showTitle : __("{$langFile}.show_title")  }}</x-ui.breadcrumb-item>
        </x-ui.breadcrumb>
      @else
        <span>{{ __("{$langFile}.show_title") }}</span>
      @endif

      @isset($bottomHeader)
        @include($bottomHeader)
      @endisset
    </x-slot>

    <x-ui.card :class="$formWidth ?? 'max-w-lg'">
      @include($view)

      <div class="flex justify-end space-x-2 mt-4">
        @if (!($notEditable ?? false) && Route::has($baseRoute . '.edit') && Auth::user()->can($permission . '.update'))
          <x-ui.button type="link" :href="route($baseRoute . '.edit', array_merge([$resource->id], $baseRouteParams ?? []))">
            {{ __('actions.edit') }}
          </x-ui.button>
        @endif

        <x-ui.back-button :fallback="url('/')" />
      </div>
    </x-ui.card>

  </div>


</x-dynamic-component>
