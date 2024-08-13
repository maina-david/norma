<div class="text-left">
  <x-ui.input :value="old('fname', $resource->fname ?? '')" name="fname" label="{{ __('auth.user.fname') }}" required />

  <x-ui.input :value="old('sname', $resource->sname ?? '')" name="sname" label="{{ __('auth.user.sname') }}" required />

  <x-ui.input type="email" :value="old('email', $resource->email ?? '')" name="email" label="{{ __('auth.user.email') }}" required />

  <div class="flex space-x-2 items-end w-full">
    <div class="flex-grow">
      <x-geonames.country-code-selector
          :label="__('auth.user.phone_mobile_country_code')"
          name="mobile_country_code"
          value="{{ $resource->mobile_country_code ?? '' }}" />
    </div>

    <div class="flex-grow">
      <x-ui.input :value="old('phone_mobile', $resource->phone_mobile ?? '')" name="phone_mobile"
                  label="{{ __('auth.user.phone_mobile') }}" />
    </div>

  </div>

  <x-ui.input x-init="if({{ !isset($resource) ? 'true' : 'false' }}) $el.value = Intl.DateTimeFormat().resolvedOptions().timeZone"
              class="user-timezone-select"
              type="select"
              :value="old('timezone', $resource->timezone ?? '')"
              name="timezone"
              :options="DateTimeZone::listIdentifiers()"
              label="{{ __('auth.user.timezone') }}" />

  @can('access.org.settings.all')
    <x-ui.input
        type="select"
        :value="old('user_type', $resource->user_type ?? '')"
        :options="\App\Enums\Auth\UserType::forSelector()"
        name="user_type"
        label="{{ __('auth.user.user_type.user_type') }}"
        required
    />

    <x-ui.input
        type="select"
        :value="old('user_roles', $resource->user_roles ?? [])"
        :options="$roles"
        name="user_roles[]"
        label="{{ __('auth.user.user_roles') }}"
        required
        multiple
    />
  @endcan

  <x-slot name="footer">
    <div></div>
    <x-ui.button size="xl" type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
  </x-slot>
</div>
