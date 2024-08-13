@props(['label', 'location', 'showPath', 'name' => 'location_id', 'required' => false, 'value' => null, 'options' => [], 'multiple' => false])
@php
$route = \App\Enums\Application\ApplicationType::collaborate()->is(\App\Managers\AppManager::getApp())
    ? route('collaborate.locations.json.index')
    : route('my.settings.locations.json.index');
@endphp
<x-ui.input :name="$name"
            :multiple="$multiple"
            :required="$required"
            label="{{ $label ?? __('geonames.location.jurisdiction') }}"
            :value="old($name, $value ?? '')"
            type="select"
            :options="isset($location)
                ? [
                    $location->id =>
                        $location->title .
                        ' (' .
                        ($location->locationType ? __('corpus.location_type.' . $location->locationType->adjective_key) : '') .
                        ')',
                ]
                : $options"
            :data-load="$route"
            :data-load-start-searching="2"
            @change="$dispatch('changed', $el.value)"
            {{ $attributes->merge(['class' => 'remote-select']) }} />

@if (isset($location) && isset($showPath) && $showPath)
  @php
    $location->load(['ancestors.type']);
  @endphp
  <div class="text-libryo-gray-500 italic">{{ $location->getBreadcrumbs() }}</div>
@endif
