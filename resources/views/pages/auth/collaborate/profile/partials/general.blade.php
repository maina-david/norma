<div class="flex w-full">
  <div class="space-y-1 grow">
    <h3 class="text-lg leading-6 font-medium text-norma-gray-900">{{ __('collaborators.profile.information') }}</h3>
    <p class="max-w-2xl text-sm text-norma-gray-500">{{ __('collaborators.profile.information_description') }}</p>
  </div>

  <x-ui.form
             class="max-w-lg grow space-y-2"
             method="POST"
             action="{{ route('collaborate.profile.update') }}">
    @method('PUT')
    <x-ui.input
                value="{{ old('fname', $user->fname) }}"
                name="fname"
                label="{{ __('users.first_name') }}"
                required />
    <x-ui.input
                value="{{ old('sname', $user->sname) }}"
                name="sname"
                label="{{ __('users.last_name') }}" />

    <x-geonames.country-code-selector
                                      :label="__('users.country_code')"
                                      name="country_code"
                                      value="{{ $user->mobile_country_code }}" />

    <div class="flex my-2 justify-end">
      <x-ui.button
                   type="submit"
                   theme="primary">Save</x-ui.button>
    </div>
  </x-ui.form>
</div>
