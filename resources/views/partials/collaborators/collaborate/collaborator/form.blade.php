@php
  use App\Models\Collaborators\Team;

  $teams = [-1 =>  __('collaborators.team.create_personal_team')];
  $teams = $teams + Team::orderBy('title')->pluck('title', 'id')->toArray();
@endphp

<div class="mt-4">
  <div>
    <x-ui.input
        required
        :label="__('collaborators.first_name')"
        :value="old('fname', $resource->fname ?? '')"
        name="fname"
    />
  </div>

  <div>
    <x-ui.input
        required
        :label="__('collaborators.last_name')"
        :value="old('sname', $resource->sname ?? '')"
        name="sname"
    />
  </div>

  <div>
    <x-ui.input
        required
        :label="__('collaborators.email')"
        :value="old('email', $resource->email ?? '')" name="email"
        type="email"
    />
  </div>

  <div x-data="{team: -1}">
    <x-ui.input
        required
        :label="__('collaborators.team.team')"
        :value="old('team_id', $resource->team_id ?? '')"
        name="team_id"
        type="select"
        :tomselect="false"
        :options="$teams"
        x-model="team"
    />

    <div class="mt-2" x-show="team != -1">
      <x-ui.input
        type="checkbox"
        :label="__('collaborators.collaborator.team_admin')"
        :value="old('is_team_admin', $resource->profile->is_team_admin ?? false)"
        name="is_team_admin"
      />
    </div>
  </div>

  <div>
    <x-ui.input
      required
      :label="__('collaborators.group.index_title')"
      :value="old('groups')"
      name="groups[]"
      type="select"
      :options="\App\Models\Collaborators\Group::orderBy('title')->pluck('title', 'id')->toArray()"
      multiple
    />
  </div>

  <div>
    <x-auth.role.role-selector
      name="roles[]"
      :label="__('auth.user.user_roles')"
      multiple
    />
  </div>

</div>

<div class="flex justify-between mt-4">
  <div>
    @if(request()->routeIs('collaborate.collaborators.create'))
      <x-ui.modal>
        <x-slot:trigger>
          <x-ui.button type="button" styling="outline" @click="open=true">
            {{ __('collaborators.import_existing') }}
          </x-ui.button>
        </x-slot:trigger>

        <form action=""></form>

        <div class="max-w-sm w-screen">
          <div class="font-semibold">{{ __('collaborators.import_existing') }}</div>

          <x-ui.form method="POST" :action="route('collaborate.collaborators.import')">

            <x-ui.input name="email" :label="__('collaborators.email')" type="email" required />

            <div class="mt-4 flex justify-end">
              <x-ui.button type="submit">
                {{ __('actions.import') }}
              </x-ui.button>
            </div>

          </x-ui.form>
        </div>
      </x-ui.modal>
    @endif
  </div>

  <div class="flex justify-end space-x-4">
    <x-ui.back-button />
    <x-ui.button type="submit">
      {{ __('actions.save') }}
    </x-ui.button>
  </div>
</div>


