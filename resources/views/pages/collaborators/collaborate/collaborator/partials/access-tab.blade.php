@php
  $teams = \App\Models\Collaborators\Team::orderBy('title')->pluck('title', 'id')->toArray();
  $teams[''] = '-';
  $groups = \App\Models\Collaborators\Group::orderBy('title')->pluck('title', 'id')->toArray();
  $groups[''] = '-';
@endphp

<div class="pt-4 mb-12">

  <x-ui.form :action="route('collaborate.collaborators.roles.update', $resource->id)" method="PUT">
    <x-auth.role.role-selector
      name="roles[]"
      :label="__('auth.user.user_roles')"
      :value="old('roles', $resource->roles->map->id->toArray())"
      multiple
    />

    <x-ui.input
      :label="__('collaborators.group.index_title')"
      :value="old('groups', $resource->groups->map->id->toArray())"
      name="groups[]"
      type="select"
      :options="$groups"
      multiple
    />

    <x-ui.input
        required
        :label="__('collaborators.team.team')"
        :value="old('team_id', $resource->profile->team_id ?? '')"
        name="team_id"
        type="select"
        :tomselect="false"
        :options="$teams"
    />

    <div class="mt-4">
      <x-ui.input
          type="checkbox"
          :label="__('collaborators.collaborator.team_admin')"
          :value="old('is_team_admin', $resource->profile->is_team_admin ?? false)"
          name="is_team_admin"
      />
    </div>

    <div class="flex mt-6">
      <x-ui.button type="submit" theme="primary" styling="outline">
        {{ __('auth.user.update_access') }}
      </x-ui.button>
    </div>
  </x-ui.form>
</div>
