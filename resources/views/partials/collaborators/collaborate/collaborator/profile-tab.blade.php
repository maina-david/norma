@php
    use App\Services\Storage\MimeTypeManager;use Carbon\Carbon;

    $hearAbout = [
        '--disabled--' => __('interface.please_select'),
        'Other' => __('interface.other'),
        'Search engine' => __('collaborators.search_engine'),
        'Social media' => __('collaborators.social_media'),
        'University careers portal' => __('collaborators.university_careers_portal'),
        'Word of mouth referral' => __('collaborators.word_of_mouth_referral'),
    ];

@endphp
<div class="mt-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <x-geonames.country-code-selector name="nationality"
                                              :value="old('nationality', $resource->profile->nationality)"
                                              label="{{ __('collaborators.nationality') }}" no-code required/>
        </div>

        <div>
            <x-geonames.country-code-selector name="current_residency"
                                              :value="old('current_residency', $resource->profile->current_residency)"
                                              label="{{ __('collaborators.current_residency') }}" no-code required/>
        </div>

        <div class=" flex">
            <div class="shrink-0">
                <x-geonames.country-code-selector
                    :value="old('mobile_country_code', $resource->profile->mobile_country_code)"
                    name="mobile_country_code" label="{{ __('collaborators.country_code') }}"/>
            </div>

            <div class="flex-grow ml-4">
                <x-ui.input :label="__('collaborators.phone')"
                            :value="old('phone_mobile', $resource->profile->phone_mobile)"
                            name="phone_mobile"/>
            </div>
        </div>

        <div>
            <x-ui.input name="hear_about" :label="__('collaborators.hear_about')" type="select"
                        :value="old('hear_about', $resource->profile->hear_about)" :options="$hearAbout"/>
        </div>

        <div>
            <x-ui.input :label="__('collaborators.university')"
                        :value="old('university', $resource->profile->university)"
                        name="university"/>
        </div>

        <div>
            <x-ui.input :label="__('collaborators.timezone')" :value="old('timezone', $resource->profile->timezone)"
                        name="timezone"/>
        </div>

        <div>
            <x-ui.input :label="__('collaborators.year_of_study')"
                        :value="old('year_of_study', $resource->profile->year_of_study)" name="year_of_study"/>
        </div>

        <div>
            <x-ui.input :label="__('collaborators.linked_in_profile')"
                        :value="old('linkedin_url', $resource->profile->linkedin_url)" name="linkedin_url"/>
        </div>

        <div>
            <x-ui.input :label="__('collaborators.address_line_1')"
                        :value="old('address_line_1', $resource->profile->address_line_1)" name="address_line_1"/>
        </div>

        <div>
            <x-ui.input :label="__('collaborators.address_line_2')"
                        :value="old('address_line_2', $resource->profile->address_line_2)" name="address_line_2"/>
        </div>

        <div>
            <x-ui.input :label="__('collaborators.address_line_3')"
                        :value="old('address_line_3', $resource->profile->address_line_3)" name="address_line_3"/>
        </div>

        <div>
            <x-geonames.country-code-selector name="address_country_code"
                                              :value="old('address_country_code', $resource->profile->address_country_code)"
                                              label="{{ __('collaborators.address_country_code') }}" no-code/>
        </div>

        <div>
            <x-ui.label for="">{{ __('auth.user.update_profile_photo') }}</x-ui.label>
            <x-ui.file-selector
                name="photo"
                :mimes="(new MimeTypeManager())->getImageMimeTypes()"
            />
        </div>

        <div>
            <x-ui.input :label="__('collaborators.postal_address')"
                        :value="old('postal_address', $resource->profile->postal_address)" name="postal_address"/>
        </div>

    </div>

    <div class="mt-4">
        <x-ui.input type="checkbox" :label="__('collaborators.available_for_work')"
                    :value="old('available', $resource->profile->available)" name="available"/>
    </div>

    <div class="mt-4">
        <x-ui.input type="checkbox" :label="__('collaborators.agreed_to_comms')"
                    :value="old('allows_communication', isset($resource) ? $resource->profile->allows_communication : true)"
                    name="allows_communication"/>
    </div>
</div>
