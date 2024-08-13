<x-layouts.settings>
  <x-slot name="header">
    <x-ui.icon name="books" size="10" class="mr-5 text-libryo-gray-600" /> {{ $library->title }}
  </x-slot>

  <x-slot name="actions">
    <div class="inline-flex">
      <x-ui.delete-button :route="route('my.settings.libraries.destroy', ['library' => $library->id])"
                          button-theme="negative">{{ __('actions.delete') }}</x-ui.delete-button>
    </div>

    <x-ui.button styling="outline"
                 theme="primary"
                 type="link"
                 href="{{ route('my.settings.libraries.edit', ['library' => $library->id]) }}">
      {{ __('actions.edit') }}</x-ui.button>
  </x-slot>

  <x-ui.card>
    <x-ui.tabs>

      <x-slot name="nav">
        <x-ui.tab-nav name="libryos" :url="route('my.settings.libryos.for.library.index', ['library' => $library->id])">
          {{ __('settings.nav.libryo_streams') }}</x-ui.tab-nav>
        <x-ui.tab-nav name="children"
                      :url="route('my.settings.children-parents.for.library.index', [
                          'library' => $library->id,
                          'relation' => 'children',
                      ])">
          {{ __('compilation.library.children') }}</x-ui.tab-nav>
        <x-ui.tab-nav name="parents"
                      :url="route('my.settings.children-parents.for.library.index', [
                          'library' => $library->id,
                          'relation' => 'parents',
                      ])">
          {{ __('compilation.library.parents') }}</x-ui.tab-nav>
        <x-ui.tab-nav name="requirements"
                      :url="route('my.settings.works.for.library.index', ['library' => $library->id])">
          {{ __('settings.nav.requirements') }}</x-ui.tab-nav>
      </x-slot>

      <x-ui.tab-content name="libryos">
        <turbo-frame id="settings-libryos-for-library-{{ $library->id }}">
          <x-ui.skeleton class="mt-5" />
        </turbo-frame>
      </x-ui.tab-content>
      <x-ui.tab-content name="children">
        <turbo-frame id="settings-children-for-library-{{ $library->id }}">
          <x-ui.skeleton class="mt-5" />
        </turbo-frame>
      </x-ui.tab-content>
      <x-ui.tab-content name="parents">
        <turbo-frame id="settings-parents-for-library-{{ $library->id }}">
          <x-ui.skeleton class="mt-5" />
        </turbo-frame>
      </x-ui.tab-content>
      <x-ui.tab-content name="requirements">
        <turbo-frame id="settings-works-for-library-{{ $library->id }}">
          <x-ui.skeleton class="mt-5" />
        </turbo-frame>
      </x-ui.tab-content>

    </x-ui.tabs>
  </x-ui.card>
</x-layouts.settings>
