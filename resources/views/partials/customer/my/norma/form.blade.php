@php use App\Services\Customer\ActiveOrganisationManager; @endphp
<x-ui.input :value="old('title', $resource->title ?? '')" name="title" label="{{ __('interface.title') }}" required/>

@if (userCanManageAllOrgs())
<div class="my-4">
  <x-ui.input
      type="checkbox"
      :value="old('demo', $resource->demo ?? false)"
      name="demo"
      label="{{ __('customer.norma.demo') }}"
  />
</div>
@endif

<x-ui.input :value="old('address', $resource->address ?? '')" name="address"
            label="{{ __('customer.norma.address') }}"/>

<x-ui.input :value="old('geo_lat', $resource->geo_lat ?? '')" name="geo_lat"
            label="{{ __('customer.norma.geo_lat') }}"/>

<x-ui.input :value="old('geo_lng', $resource->geo_lng ?? '')" name="geo_lng"
            label="{{ __('customer.norma.geo_lng') }}"/>

@if (userCanManageAllOrgs())
  <x-customer.norma-type.norma-type-selector
      required
      :value="old('place_type_id', $resource->place_type_id ?? null)"
  />

  <x-ui.input
      :value="old('economic_category_id', $resource->economic_category_id ?? null)"
      name="economic_category_id"
      type="select"
      required
      :options="$categories"
      :option-details="$categoryDescriptions"
      :placeholder="__('customer.norma.industry')"
  >
    <x-slot:label>
      <span class="flex items-center justify-between flex-grow">
        <span>{{ __('customer.norma.industry') }}</span>
        <a class="ml-1 -mb-1 tippy" data-tippy-content="Get help" target="_blank" href="https://2566833.fs1.hubspotusercontent-na1.net/hubfs/2566833/Stream%20Industry%20Field.pdf">
          <x-ui.icon name="circle-question" size="4" />
        </a>
      </span>
    </x-slot:label>

  </x-ui.input>



  <x-ui.input type="textarea" :value="old('description', $resource->description ?? '')" name="description"
              label="{{ __('interface.description') }}"/>

  <x-compilation.library.library-selector :id="isset($resource) ? $resource->library_id : null"
                                          :title="isset($resource) && $resource->library_id ? $resource->library->title : null"/>

  @if(!app(ActiveOrganisationManager::class)->isSingleOrgMode())
    <x-customer.organisation.organisation-selector
        :id="isset($resource) ? $resource->organisation_id : null"
        :title="isset($resource) && $resource->organisation_id ? $resource->organisation->title : null"/>
  @endif


  <x-geonames.location.location-selector required :location="isset($resource) ? $resource->location : null" show-path/>


  <div class="mb-10">
    <div class="text-lg mb-3">{{ __('customer.norma.compilation') }}</div>
{{--    <x-ui.input type="checkbox"--}}
{{--                :value="old('compilation_in_progress', isset($resource) ? $resource->compilation_in_progress : false)"--}}
{{--                name="compilation_in_progress"--}}
{{--                label="{{ __('customer.norma.compilation_in_progress') }}"/>--}}
    <x-ui.input type="checkbox"
                :value="old('needs_recompilation', isset($resource) ? $resource->needs_recompilation : false)"
                name="needs_recompilation"
                label="{{ __('customer.norma.needs_recompilation') }}"/>
    <x-ui.input type="checkbox"
                :value="old('auto_compiled', isset($resource) ? $resource->auto_compiled : true)"
                name="auto_compiled"
                label="{{ __('customer.norma.auto_compiled') }}"/>

  </div>
@endif

<x-slot name="footer">
  <div></div>
  <div>
    <x-ui.back-button
        :fallback="isset($resource) && $resource->id ? route('my.settings.normas.show', ['norma' => $resource->id]) : route('my.settings.normas.index')"/>
    <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
  </div>
</x-slot>
