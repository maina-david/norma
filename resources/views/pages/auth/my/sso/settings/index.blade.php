<x-layouts.settings>

  <div class="container mx-auto">
    <x-ui.card>
      <div class="grid grid-cols-5 gap-8">
        <div class="col-span-5 lg:col-span-2">
          <div class="text-xl font-semibold text-libryo-gray-900">{{ __('auth.saml_sso') }}</div>
          <div class="mt-2">{{ __('auth.saml_sso_description') }}</div>
        </div>

        <div class="col-span-5 lg:col-span-3">
          <x-ui.form method="PUT" :action="route('my.settings.sso.update')">

            <p>{{ __('auth.saml.setup_saml') }}</p>
            <ol class="list-decimal list-inside pl-4 my-2 space-y-1">
              <li>{!! __('auth.saml.setup_saml_step_1') !!}</li>
              <li>{!! __('auth.saml.setup_saml_step_2') !!}</li>
              <li>{!! __('auth.saml.setup_saml_step_3') !!}</li>
              <li>{!! __('auth.saml.setup_saml_step_4') !!}</li>
              <li>{!! __('auth.saml.setup_saml_step_5') !!}</li>
            </ol>


            <div class="mt-8">
              <div class="font-semibold text-lg border-b border-libryo-gray-200 pb-2 mb-8">
                {{  __('auth.saml.service_provider') }}
              </div>

              <x-ui.input
                :label="__('auth.saml.assertion_consumer_service_url')"
                value="{{ route('my.saml.login.complete', ['slug' => $organisation->slug]) }}"
                name=""
                readonly
                @click="$event.target.setSelectionRange(0, $event.target.value.length)"
              />

              <x-ui.input
                :label="__('auth.saml.identifier')"
                value="{{ route('my.saml.metadata.index', ['slug' => $organisation->slug]) }}"
                name=""
                readonly
                @click="$event.target.setSelectionRange(0, $event.target.value.length)"
              />

              <x-ui.input
                :label="__('auth.saml.metadata_url')"
                value="{{ route('my.saml.metadata.index', ['slug' => $organisation->slug]) }}"
                name=""
                readonly
                @click="$event.target.setSelectionRange(0, $event.target.value.length)"
              />

              <x-ui.input
                :label="__('auth.saml.sso_url')"
                value="{{ route('my.saml.login.index', ['slug' => $organisation->slug]) }}"
                name=""
                readonly
                @click="$event.target.setSelectionRange(0, $event.target.value.length)"
              />

              <x-ui.input
                :label="__('auth.saml.slo_url')"
                value="{{ route('my.saml.logout', ['slug' => $organisation->slug]) }}"
                name=""
                readonly
                @click="$event.target.setSelectionRange(0, $event.target.value.length)"
              />

              <div class="mt-8 font-semibold text-lg border-b border-libryo-gray-200 pb-2 mb-8">
                {{  __('auth.saml.identity_provider') }}
              </div>

              <x-ui.input
                :label="__('auth.saml.entity_id')"
                name="entity_id"
                value="{{ old('entity_id', $provider->entity_id ?? '') }}"
                required
              />

              <x-ui.input
                :label="__('auth.saml.idp_sso_url')"
                name="sso_url"
                value="{{ old('sso_url', $provider->sso_url ?? '') }}"
                required
              />

              <x-ui.input
                :label="__('auth.saml.idp_slo_url')"
                name="slo_url"
                value="{{ old('slo_url', $provider->slo_url ?? '') }}"
              />

              <x-ui.input
                rows="8"
                type="textarea"
                :label="__('auth.saml.idp_certificate')"
                name="certificate"
                value="{{ old('certificate', $provider->certificate ?? '') }}"
                required
              />

            </div>


            <div class="mt-8">
              <div class="font-semibold text-lg border-b border-libryo-gray-200 pb-2 mb-8">
                {{  __('auth.saml.application_settings') }}
              </div>

              <x-customer.team.team-selector
                name="team_id"
                :label="__('auth.saml.default_team')"
                :id="$provider->team->id ?? null"
                :title="$provider->team->title ?? null"
                required
              />

              <div class="mt-4">
                <x-ui.input
                  type="checkbox"
                  :label="__('auth.saml.enable_for_organisation')"
                  name="enabled"
                  value="{{ $provider->enabled ?? false }}"
                />
              </div>
            </div>

            <div class="mt-4 flex justify-end">
              <x-ui.button type="submit" theme="primary">
                {{ __('actions.save') }}
              </x-ui.button>
            </div>


          </x-ui.form>

        </div>
      </div>

    </x-ui.card>
  </div>



</x-layouts.settings>
