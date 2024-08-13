<x-dynamic-component :component="$appLayout" :fluid="$fluid ?? false">
  @isset($pageHead)
  <x-slot name="pageHead">
    @vite($pageHead)
  </x-slot>
  @endisset

  <x-slot name="header">

    @if ($application->value === App\Enums\Application\ApplicationType::collaborate()->value)
      <div class="flex flex-col">

        @isset($preBreadcrumbView)
          @include($preBreadcrumbView)
        @endisset

        <div class="flex items-end flex-grow">
          <x-ui.breadcrumb class="-my-2">
            @if ($forResourceTitle)
              <x-ui.breadcrumb-item :route="$forResourceRoute ?? null">{{ $forResourceTitle }}</x-ui.breadcrumb-item>
            @endif
            <x-ui.breadcrumb-item>{{ $title ?? __("{$langFile}.index_title") }}</x-ui.breadcrumb-item>
          </x-ui.breadcrumb>
        </div>
      </div>
    @else
      <span>{!! $title ?? __("{$langFile}.index_title") !!}</span>
    @endif
  </x-slot>

  <x-slot name="actions">
    <div class="flex flex-col items-end">
      @isset($preCreateButtonView)
        <div class="mb-4">
          @include($preCreateButtonView)
        </div>
      @endisset

      @if ($withCreate)
        @can("{$permission}.create")
          <x-ui.button type="link" :href="route($baseRoute . '.create', $baseRouteParams ?? [])" theme="primary">
            {{ __('actions.create') }}
          </x-ui.button>
        @endcan
      @endif
    </div>
  </x-slot>

  @if (isset($view))
    @include($view)
  @else
    @include('pages.crud.index-table')
  @endif

</x-dynamic-component>
