<div>
  <div class="flex flex-wrap">
    <div class="w-full md:w-1/2 mt-2">
      <x-ui.show-field :label="__('collaborators.first_name')" :value="$resource->fname" />
    </div>
    <div class="w-full md:w-1/2 mt-2">
      <x-ui.show-field :label="__('collaborators.last_name')" :value="$resource->lname" />
    </div>
    <div class="w-full md:w-1/2 mt-2">
      <x-ui.show-field :label="__('collaborators.email')" :value="$resource->email" />
    </div>
    <div class="w-full md:w-1/2 mt-2">
      <x-ui.show-field :label="__('collaborators.nationality')" :value="$resource->profile->nationality" />
    </div>
    <div class="w-full md:w-1/2 mt-2">
      <x-ui.show-field :label="__('collaborators.current_residency')" :value="$resource->profile->current_residency" />
    </div>

    <div class="w-full md:w-1/2 mt-2 flex">
      <div class="shrink-0">
        <x-ui.show-field :label="__('collaborators.country_code')" :value="$resource->profile->mobile_country_code" />
      </div>

      <x-ui.show-field :label="__('collaborators.phone')" :value="$resource->profile->phone_mobile" />
    </div>
    <div class="w-full md:w-1/2 mt-2">
      <x-ui.show-field :label="__('collaborators.hear_about')" :value="$resource->profile->hear_about" />
    </div>

    <div class="w-full md:w-1/2 mt-2">
      <x-ui.show-field :label="__('collaborators.university')" :value="$resource->profile->university" />
    </div>
    <div class="w-full md:w-1/2 mt-2">
      <x-ui.show-field :label="__('collaborators.timezone')" :value="$resource->profile->timezone" />
    </div>

    <div class="w-full md:w-1/2 mt-2">
      <x-ui.show-field :label="__('collaborators.year_of_study')" :value="$resource->profile->year_of_study" />
    </div>
    <div class="w-full md:w-1/2 mt-2">
      <x-ui.show-field :label="__('collaborators.linked_in_profile')">
        @if ($resource->profile->linkedin_url)
          <a href="{{ $resource->profile->linkedin_url }}" target="_blank"
             class="text-primary">{{ $resource->profile->linkedin_url }}</a>
        @else
          -
        @endif
      </x-ui.show-field>
    </div>


    <div class="w-full md:w-1/2 mt-2">
      <x-ui.show-field :label="__('collaborators.address_line_1')" :value="$resource->profile->address_line_1" />
    </div>
    <div class="w-full md:w-1/2 mt-2">
      <x-ui.show-field :label="__('collaborators.address_line_2')" :value="$resource->profile->address_line_2" />
    </div>

    <div class="w-full md:w-1/2 mt-2">
      <x-ui.show-field :label="__('collaborators.address_line_3')" :value="$resource->profile->address_line_3" />
    </div>
    <div class="w-full md:w-1/2 mt-2">
      <x-ui.show-field :label="__('collaborators.address_country_code')"
                       :value="$resource->profile->address_country_code" />
    </div>

    <div class="w-full md:w-1/2 mt-2">
      <x-ui.show-field :label="__('collaborators.postal_address')" :value="$resource->profile->postal_address" />
    </div>

  </div>

  <div class="mt-4">
    <x-ui.show-field type="checkbox" :label="__('collaborators.available_for_work')"
                     :value="$resource->profile->available" />
  </div>

  <div class="mt-4">
    <x-ui.show-field type="checkbox" :label="__('collaborators.agreed_to_process_data')"
                     :value="$resource->profile->process_personal_data" />
  </div>

  <div class="mt-4">
    <x-ui.show-field type="checkbox" :label="__('collaborators.agreed_to_comms')"
                     :value="$resource->profile->allows_communication" />
  </div>

  @if($resource->active && $resource->assignedTasks()->where('updated_at', '>=', now()->subMonths(6))->exists())
      <div class="mt-4">
        <x-ui.show-field type="checkbox" :label="__('collaborators.automatic_emails_disabled')"
                         :value="false" />
      </div>
  @endif
</div>
