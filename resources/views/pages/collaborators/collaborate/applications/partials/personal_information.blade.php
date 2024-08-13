<div class="flex flex-col md:flex-row">
  <div class="grow md:mr-4 w-full md:w-1/2">
    <x-ui.input name="first_name" label="{{ __('users.first_name') }}" required autofocus />

    <x-ui.input name="email" label="{{ __('users.email') }}" required type="email" />

    <x-geonames.country-code-selector name="nationality" label="{{ __('collaborators.nationality') }}" no-code
                                      required />
  </div>

  <div class="grow md:ml-4 w-full md:w-1/2">
    <x-ui.input name="last_name" label="{{ __('users.last_name') }}" required />

    <div class="flex space-x-2">
      <div class="w-1/2">
        <x-geonames.country-code-selector name="mobile_country_code" label="{{ __('users.country_code') }}" />
      </div>

      <div class="w-1/2">
        <x-ui.input name="phone_mobile" label="{{ __('users.phone') }}" type="number" />
      </div>
    </div>

    <x-geonames.country-code-selector name="current_residency" label="{{ __('collaborators.current_residency') }}"
                                      no-code required />
  </div>
</div>

<div>
  <x-ui.input name="hear_about" label="{{ __('collaborators.hear_about') }}" type="select" :options="$hearAbout" />
</div>
