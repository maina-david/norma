<x-layouts.collaborate >
  <x-ui.card>

    @include('pages.collaborators.collaborate.collaborator.partials.collaborator-header', ['resource' => $user])

    <x-ui.tabs :active="$tab">

      <x-slot name="nav">
        <x-ui.tab-nav name="activities">{{ __('collaborators.collaborator.activities') }}</x-ui.tab-nav>
        <x-ui.tab-nav name="profile">{{ __('collaborators.personal_information') }}</x-ui.tab-nav>
        <x-ui.tab-nav name="languages">{{ __('collaborators.collaborator.languages') }}</x-ui.tab-nav>
        <x-ui.tab-nav name="documents">{{ __('collaborators.collaborator.documents') }}</x-ui.tab-nav>
        <x-ui.tab-nav name="security" href="{{ route('collaborate.profile.email.show') }}">{{ __('collaborators.collaborator.security') }}</x-ui.tab-nav>
      </x-slot>

      <x-ui.form method="PUT" :action="route('collaborate.profile.update')" enctype="multipart/form-data">
        <input name="_tab" x-model="tab" type="hidden" />
        <x-ui.tab-content name="profile">
          <div>
            @include('partials.collaborators.collaborate.collaborator.profile-tab', ['resource' => $user])

            <div class="flex justify-end">
              <x-ui.button type="submit">
                {{ __('actions.save') }}
              </x-ui.button>
            </div>
          </div>
        </x-ui.tab-content>

        <x-ui.tab-content name="languages">
          <div>
            @include('partials.collaborators.collaborate.collaborator.languages-tab', ['resource' => $user])

            <div class="flex justify-end">
              <x-ui.button type="submit">
                {{ __('actions.save') }}
              </x-ui.button>
            </div>
          </div>
        </x-ui.tab-content>

      </x-ui.form>

      <x-ui.tab-content name="documents">
        @include('partials.collaborators.collaborate.collaborator.documents-tab', ['resource' => $user, 'canUpload' => auth()->user()->can('collaborate.collaborators.collaborator.update-own-documents'), 'personal' => true])
      </x-ui.tab-content>

      <x-ui.tab-content name="activities">
        <div class="my-6">
          @include('pages.collaborators.collaborate.collaborator.partials.activities')
        </div>
      </x-ui.tab-content>

      <x-ui.tab-content name="security">
        <div class="my-6">
          @include('pages.collaborators.collaborate.collaborator.partials.security-tab')
        </div>
      </x-ui.tab-content>

    </x-ui.tabs>
  </x-ui.card>
</x-layouts.collaborate>
