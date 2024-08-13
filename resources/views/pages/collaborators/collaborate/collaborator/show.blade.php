@include('pages.collaborators.collaborate.collaborator.partials.collaborator-header')


<x-ui.tabs>

  <x-slot name="nav">
    <x-ui.tab-nav name="profile">{{ __('collaborators.collaborator.profile') }}</x-ui.tab-nav>
    <x-ui.tab-nav name="languages">{{ __('collaborators.collaborator.languages') }}
    </x-ui.tab-nav>
    <x-ui.tab-nav name="documents">{{ __('collaborators.collaborator.documents') }}</x-ui.tab-nav>
    @can('collaborate.collaborators.collaborator.manage-access')
    <x-ui.tab-nav name="access">{{ __('collaborators.collaborator.access') }}</x-ui.tab-nav>
    @endcan
    <x-ui.tab-nav name="activities">{{ __('collaborators.collaborator.activities') }}</x-ui.tab-nav>
  </x-slot>

  <x-ui.tab-content name="profile">
    @include('pages.collaborators.collaborate.collaborator.partials.profile-tab')
  </x-ui.tab-content>

  <x-ui.tab-content name="languages">
    @include('pages.collaborators.collaborate.collaborator.partials.languages-tab')
  </x-ui.tab-content>

  <x-ui.tab-content name="documents">
    @include('pages.collaborators.collaborate.collaborator.partials.documents-tab')
  </x-ui.tab-content>

  <x-ui.tab-content name="activities">
    @include('pages.collaborators.collaborate.collaborator.partials.activities-tab')
  </x-ui.tab-content>

  @can('collaborate.collaborators.collaborator.manage-access')
  <x-ui.tab-content name="access">
    @include('pages.collaborators.collaborate.collaborator.partials.access-tab')
  </x-ui.tab-content>
  @endcan

</x-ui.tabs>
