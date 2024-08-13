<div>
  <div class="font-semibold text-lg text-libryo-gray-900">
    {{ $organisation->title }} - {{ __('auth.user.import.create_title') }}
  </div>
  <p class="text-sm mt-2">
    {{ __('auth.user.import.explanation') }}
  </p>
</div>

<x-customer.team.team-selector multiple />

<x-ui.input name="users" label="{{ __('storage.upload_files') }}" type="file" accept=".xls,.xlsx" required />

<x-slot name="footer">
  <div>
    <x-ui.button styling="outline"
                 theme="primary"
                 type="link"
                 target="_blank"
                 href="{{ route('my.settings.users.for.organisation.import.template') }}"
    >
      {{ __('actions.download_template') }}
    </x-ui.button>
  </div>
  <div>
    <x-ui.back-button
        :fallback="isset($resource) && $resource->id ? route('my.settings.teams.show', ['team' => $resource->id]) : route('my.settings.teams.index')" />
    <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
  </div>
</x-slot>
