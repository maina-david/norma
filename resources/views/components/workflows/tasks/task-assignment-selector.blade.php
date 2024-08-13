@props(['name', 'label' => null, 'required' => false, 'value' => null, 'user' => null])

<x-ui.input
  :name="$name"
  :required="$required"
  label="{{ $label }}"
  :value="old($name, $value ?? '')"
  type="select"
  :options="isset($user)? [$user->id => $user->full_name] : []"
  :data-load="route('collaborate.collaborators.json.index')"
  :data-load-start-searching="4"
  @change="$dispatch('changed', $el.value)"
  {{ $attributes->merge(['class' => 'remote-select']) }}
/>
