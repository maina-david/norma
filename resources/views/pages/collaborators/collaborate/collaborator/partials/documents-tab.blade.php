@php
use App\Enums\Collaborators\ContractType;
use App\Enums\Collaborators\DocumentType;
use App\Enums\Collaborators\IdentityDocumentType;

@endphp
<div>
  <div class="flex flex-wrap">
    @if($showIdentities ?? true)
      <div class="w-full md:w-1/2 mt-2 border-b border-libryo-gray-100 pb-4">
        @include('pages.collaborators.collaborate.collaborator.partials.attachment-field', [
          'label' => __('collaborators.identity_document'),
          'type' => DocumentType::IDENTIFICATION,
          'subtypes' => IdentityDocumentType::forSelector(),
          'defaultTextField' => __('collaborators.national_id'),
        ])
      </div>

      <div class="w-full md:w-1/2 mt-2 border-b border-libryo-gray-100 pb-4">
        @include('pages.collaborators.collaborate.collaborator.partials.attachment-field', [
          'label' => __('collaborators.Visa'),
          'type' => DocumentType::VISA,
          'validationPartial' => 'partials.collaborators.collaborate.collaborator.validate-visa',
          'defaultTextField' => ($resource->profile->visa->expired ?? false) ? null : __('collaborators.valid'),
          'revalidate' => true,
        ])
      </div>
    @endif

    <div class="w-full md:w-1/2 mt-2 border-b border-libryo-gray-100 pb-4">
      @include('pages.collaborators.collaborate.collaborator.partials.attachment-field', [
        'label' => __('collaborators.curriculum_vitae'),
        'type' => DocumentType::VITAE,
        'defaultTextField' => __('collaborators.valid'),
      ])
    </div>

    <div class="w-full md:w-1/2 mt-2 border-b border-libryo-gray-100 pb-4">
      @include('pages.collaborators.collaborate.collaborator.partials.attachment-field', [
        'label' => __('collaborators.education_transcript'),
        'type' => DocumentType::TRANSCRIPT,
        'defaultTextField' => __('collaborators.valid'),
      ])
    </div>
    <div class="w-full md:w-1/2 mt-2 border-b border-libryo-gray-100 pb-4">
      @include('pages.collaborators.collaborate.collaborator.partials.attachment-field', [
        'label' => __('collaborators.contract'),
        'type' => DocumentType::CONTRACT,
        'defaultTextField' => ContractType::lang()[$resource->profile->contract->subtype ?? '-'] ?? '-',
        'suffix' => $resource->profile->contract_signed ? __('collaborators.signed') : __('collaborators.not_signed'),
        'uploadFields' => 'pages.collaborators.collaborate.collaborator.partials.contract-fields',
      ])
    </div>

    @if(ContractType::zeroHour()->value == ($resource->profile->contract->subtype ?? '-'))
      <div class="w-full md:w-1/2 mt-2 border-b border-libryo-gray-100 pb-4">
        @include('pages.collaborators.collaborate.collaborator.partials.attachment-field', [
          'label' => __('collaborators.schedule_a'),
          'type' => DocumentType::SCHEDULE_A,
          'defaultTextField' => __('collaborators.valid'),
        ])
      </div>

      <div class="w-full md:w-1/2 mt-2 border-b border-libryo-gray-100 pb-4">
        @include('pages.collaborators.collaborate.collaborator.partials.attachment-field', [
          'label' => __('collaborators.term_dates'),
          'type' => DocumentType::TERM_DATES,
          'defaultTextField' => __('collaborators.valid'),
        ])
      </div>
    @endif

  </div>
</div>
