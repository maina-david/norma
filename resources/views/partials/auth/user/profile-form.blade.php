@php
use App\Services\Storage\MimeTypeManager;
@endphp

<x-ui.input :value="old('fname', $resource->fname ?? '')" name="fname" label="{{ __('auth.user.fname') }}" required />
<x-ui.input :value="old('sname', $resource->sname ?? '')" name="sname" label="{{ __('auth.user.sname') }}" required />


<x-geonames.country-code-selector
                                  :label="__('auth.user.phone_mobile_country_code')"
                                  name="mobile_country_code"
                                  value="{{ $resource->mobile_country_code ?? '' }}" />

<x-ui.input :value="old('phone_mobile', $resource->phone_mobile ?? '')" name="phone_mobile"
            label="{{ __('auth.user.phone_mobile') }}" />

<x-ui.input x-init="if({{ !isset($resource) ? 'true' : 'false' }}) $el.value = Intl.DateTimeFormat().resolvedOptions().timeZone"
            class="user-timezone-select"
            type="select"
            :value="old('timezone', $resource->timezone ?? '')"
            name="timezone"
            :options="DateTimeZone::listIdentifiers()"
            label="{{ __('auth.user.timezone') }}" />

<div class="my-16 flex flex-row items-center">
  <div>
    <x-ui.user-avatar :user="$resource"
                      :dimensions="48" />
  </div>
  <div class="ml-5">
    <div class="mb-2 w-48">
      {{ __('auth.user.update_profile_photo') }}
    </div>
    <input type="file" name="profile_photo" accept="{{ $photoMimeTypes }}" />
  </div>
</div>


<x-slot name="footer">
  <div></div>
  <x-ui.button size="xl" type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
</x-slot>
