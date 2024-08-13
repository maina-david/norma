@php use App\Enums\Collaborators\ApplicationStatus; @endphp
@php use App\Enums\Collaborators\DocumentType; @endphp
@php use App\Enums\Collaborators\ApplicationStage; @endphp
<div>
  <div class="flex items-center justify-center space-x-1 font-semibold text-lg mb-6">
    <div>{{ __('collaborators.collaborator_application.stage') }} {{ ApplicationStage::fromValue($resource->stage)->label() }}
      :
    </div>
    <div>{{ ApplicationStatus::fromValue($resource->application_state)->label() }}</div>
  </div>
  <x-ui.tabs>

    <x-slot name="nav">
      <x-ui.tab-nav name="profile">{{ __('collaborators.collaborator.profile') }}</x-ui.tab-nav>
      <x-ui.tab-nav name="languages">{{ __('collaborators.collaborator.languages') }}</x-ui.tab-nav>
      <x-ui.tab-nav name="documents">{{ __('collaborators.collaborator.documents') }}</x-ui.tab-nav>
      <x-ui.tab-nav name="quiz">{{ __('collaborators.collaborator.skills_and_quiz') }}</x-ui.tab-nav>
    </x-slot>

    <x-ui.tab-content name="profile">
      @include('pages.collaborators.collaborate.collaborator.partials.profile-tab')
    </x-ui.tab-content>

    <x-ui.tab-content name="languages">
      @include('pages.collaborators.collaborate.collaborator.partials.languages-tab')
    </x-ui.tab-content>

    <x-ui.tab-content name="documents">
      @include('pages.collaborators.collaborate.collaborator.partials.documents-tab', ['forApplication' => true, 'showIdentities' => !ApplicationStage::one()->is($resource->stage)])
    </x-ui.tab-content>

    <x-ui.tab-content name="quiz">
      <div>
        <x-ui.show-field label="{{ __('collaborators.collaborator.skills_to_develop') }}">
          <div>
            @foreach($resource->profile->skills_develop as $skill)
              <x-ui.badge>{{ $skill }}</x-ui.badge>
            @endforeach
          </div>
        </x-ui.show-field>

        <x-ui.show-field label="{{ __('collaborators.collaborator.quiz') }}">
          <x-ui.collaborate.wysiwyg-content :content="$resource->text_quiz"/>
        </x-ui.show-field>
      </div>
    </x-ui.tab-content>

  </x-ui.tabs>

  @if(ApplicationStatus::pending()->is($resource->application_state))
    @if(ApplicationStage::one()->is($resource->stage) || (ApplicationStage::two()->is($resource->stage) && $resource->profile->documents()->where('type', DocumentType::IDENTIFICATION->value)->whereNotNull('approved_at')->exists()))
      @can('collaborate.collaborators.collaborator-application.update')
        <div class="flex justify-end space-x-2">
          <x-ui.confirm-button
              styling="outline"
              button-theme="primary"
              method="POST"
              route="{{ route('collaborate.collaborator-applications.approve', ['application' => $resource->id]) }}"
              label="{{ __('collaborators.collaborator_application.approve') }}"
              confirmation="{{ __('collaborators.collaborator_application.approve_confirmation') }}"
          >
            @if(ApplicationStage::one()->is($resource->stage))
              {{ __('collaborators.collaborator_application.approve') }}
            @else
              {{ __('collaborators.collaborator_application.approve_and_create') }}
            @endif
          </x-ui.confirm-button>

          <x-ui.confirm-button
              danger
              styling="outline"
              button-theme="secondary"
              method="DELETE"
              route="{{ route('collaborate.collaborator-applications.approve', ['application' => $resource->id]) }}"
              label="{{ __('collaborators.collaborator_application.decline') }}"
              confirmation="{{ __('collaborators.collaborator_application.decline_confirmation') }}"
          >
            {{ __('collaborators.collaborator_application.decline') }}
          </x-ui.confirm-button>
        </div>
      @endcan
    @endif
  @endif

  @if(ApplicationStatus::approved()->is($resource->application_state) && ApplicationStage::one()->is($resource->stage))
    @can('collaborate.collaborators.collaborator-application.update')
      <div class="flex justify-end space-x-2">
        <x-ui.confirm-button
          styling="outline"
          button-theme="primary"
          method="POST"
          route="{{ route('collaborate.collaborator-applications.resend', ['application' => $resource->id]) }}"
          label="{{ __('collaborators.collaborator_application.resent_mail') }}"
          confirmation="{{ __('collaborators.collaborator_application.resent_mail_confirmation') }}"
        >
          {{ __('collaborators.collaborator_application.resent_mail') }}
        </x-ui.confirm-button>
      </div>
    @endcan
  @endif

  @if(ApplicationStatus::declined()->is($resource->application_state))
    @can('collaborate.collaborators.collaborator-application.update')
      <div class="flex justify-end space-x-2">
        <x-ui.confirm-button
          styling="outline"
          button-theme="info"
          method="POST"
          route="{{ route('collaborate.collaborator-applications.revert', ['application' => $resource->id]) }}"
          label="{{ __('collaborators.collaborator_application.revert_to_pending') }}"
          confirmation="{{ __('collaborators.collaborator_application.revert_to_pending_confirmation') }}"
        >
          {{ __('collaborators.collaborator_application.revert_to_pending') }}
        </x-ui.confirm-button>
      </div>
    @endcan
  @endif

</div>

