@props(['id', 'title', 'multiple' => false, 'name' => 'team_id[]', 'label' => __('customer.team.teams')])

<x-ui.input
  :name="$name"
  required
  :multiple="$multiple"
  :label="$label"
  :value="old('team_id', $id ?? '')"
  type="select"
  :options="isset($title) && isset($id) ? [$id => $title] : []"
  class="remote-select"
  :data-load="route('my.settings.teams.index')"
/>
