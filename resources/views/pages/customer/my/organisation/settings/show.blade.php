<x-layouts.settings>
  <x-slot name="header">
    <span class="flex items-center">
      <x-ui.icon name="sitemap" size="10" class="mr-5 text-norma-gray-600" type="duotone" />
      <span>{{ $organisation->title }}</span>
    </span>
  </x-slot>

  <x-slot name="actions" class="flex">
    <x-ui.button styling="outline"
                 theme="primary"
                 type="link"
                 href="{{ route('my.settings.organisations.edit', ['organisation' => $organisation->id]) }}">
      {{ __('actions.edit') }}</x-ui.button>

    @if($organisation->created_at->gte(now()->subMonth()))
      <div class="inline-flex">
        <x-ui.delete-button
          :route="route('my.settings.organisations.destroy', ['organisation' => $organisation->id])"
          button-theme="negative"
        >
          {{ __('actions.delete') }}
        </x-ui.delete-button>
      </div>
    @endif
  </x-slot>

  <x-ui.card>
    <x-ui.tabs :active="$activeTab ?? 'users'">

      <x-slot name="nav">
        <x-ui.tab-nav name="users">
          {{ __('settings.nav.users') }}</x-ui.tab-nav>

        @if($organisation->is_parent)
          <x-ui.tab-nav name="children">
            {{ __('settings.nav.child_organisations') }}
          </x-ui.tab-nav>
        @endif

        <x-ui.tab-nav name="normas">
          {{ __('settings.nav.norma_streams') }}
        </x-ui.tab-nav>
        <x-ui.tab-nav name="teams">
          {{ __('settings.nav.teams') }}</x-ui.tab-nav>

        @if ($organisation->hasAssessModule())
          <x-ui.tab-nav name="assess">
            {{ __('settings.nav.assess_setup') }}</x-ui.tab-nav>
        @endif
        @if (userCanManageAllOrgs())
          <x-ui.tab-nav name="modules">
            {{ __('settings.nav.modules') }}</x-ui.tab-nav>
        @endif
      </x-slot>

      <x-ui.tab-content name="users">
        <turbo-frame loading="lazy" id="settings-users-for-organisation-{{ $organisation->id }}"
                     src="{{ route('my.settings.users.for.organisation.index', ['organisation' => $organisation->id]) }}">
          <x-ui.skeleton />
        </turbo-frame>
      </x-ui.tab-content>

      @if($organisation->is_parent)
        <x-ui.tab-content name="children">
          <turbo-frame loading="lazy" id="settings-children-for-organisation-{{ $organisation->id }}"
                       src="{{ route('my.settings.child-organisations.for.organisation.index', ['organisation' => $organisation->id]) }}">
            <x-ui.skeleton />
          </turbo-frame>
        </x-ui.tab-content>
      @endif

      <x-ui.tab-content name="normas">
        <turbo-frame loading="lazy" id="settings-normas-for-organisation-{{ $organisation->id }}"
                     src="{{ route('my.settings.normas.for.organisation.index', ['organisation' => $organisation->id]) }}">
          <x-ui.skeleton />
        </turbo-frame>
      </x-ui.tab-content>

      <x-ui.tab-content name="teams">
        <turbo-frame loading="lazy" id="settings-teams-for-organisation-{{ $organisation->id }}"
                     src="{{ route('my.settings.teams.for.organisation.index', ['organisation' => $organisation->id]) }}">
          <x-ui.skeleton />
        </turbo-frame>
      </x-ui.tab-content>

      @if ($organisation->hasAssessModule())
        <x-ui.tab-content name="assess">
          <turbo-frame loading="lazy" id="settings-assess-setup-for-organisation-{{ $organisation->id }}"
                       src="{{ route('my.settings.assess.setup.for.organisation', ['organisation' => $organisation]) }}">
            <x-ui.skeleton />
          </turbo-frame>
        </x-ui.tab-content>
      @endif

      @if (userCanManageAllOrgs())
        <x-ui.tab-content name="modules">
          <turbo-frame loading="lazy" id="settings-modules-for-organisation-{{ $organisation->id }}"
                       src="{{ route('my.settings.organisations.modules.index', ['organisation' => $organisation->id]) }}">
            <x-ui.skeleton />
          </turbo-frame>
        </x-ui.tab-content>
      @endif

    </x-ui.tabs>
  </x-ui.card>
</x-layouts.settings>
