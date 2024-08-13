@php use App\Enums\Collaborators\DocumentType; @endphp
@php use App\Enums\Collaborators\ContractType; @endphp
<div>
  <div class="flex flex-wrap">
    <div class="w-full md:w-1/2 mt-2">
      @include('pages.collaborators.collaborate.collaborator.partials.attachment-field', [
        'label' => __('collaborators.identity_document'),
        'type' => DocumentType::IDENTIFICATION,
        'defaultTextField' => __('collaborators.identity_document'),
      ])
    </div>

    <div class="w-full md:w-1/2 mt-2">
      @include('pages.collaborators.collaborate.collaborator.partials.attachment-field', [
        'label' => __('collaborators.Visa'),
        'type' => DocumentType::VISA,
        'defaultTextField' => ($resource->profile->visa->expired ?? false) ? null : __('collaborators.valid'),
      ])
    </div>

    <div class="w-full md:w-1/2 mt-2">
      @include('pages.collaborators.collaborate.collaborator.partials.attachment-field', [
        'label' => __('collaborators.curriculum_vitae'),
        'type' => DocumentType::VITAE,
        'defaultTextField' => __('collaborators.valid'),
      ])
    </div>

    <div class="w-full md:w-1/2 mt-2">
      @include('pages.collaborators.collaborate.collaborator.partials.attachment-field', [
        'label' => __('collaborators.education_transcript'),
        'type' => DocumentType::TRANSCRIPT,
        'defaultTextField' => __('collaborators.valid'),
      ])
    </div>

    @can('collaborate.collaborators.collaborator.update-documents')

      <div class="w-full md:w-1/2 mt-2">
        @include('pages.collaborators.collaborate.collaborator.partials.attachment-field', [
        'label' => __('collaborators.contract'),
        'type' => DocumentType::CONTRACT,
        'defaultTextField' => ContractType::lang()[$resource->profile->contract->subtype ?? '-'] ?? '-',
        'suffix' => $resource->profile->contract_signed ? __('collaborators.signed') : __('collaborators.not_signed')
      ])
      </div>
      {{--      @if(\App\Enums\Collaborators\ContractType::zeroHour()->is($resource->profile->contract_type))--}}
      {{--    <div class="w-full md:w-1/2 mt-2">--}}
      {{--      @include('pages.collaborators.collaborate.collaborator.partials.attachment-field', [--}}
      {{--        'label' => __('collaborators.schedule_a'),--}}
      {{--        'type' => 'schedule-a',--}}
      {{--        'attachmentField' => 'schedule_a_attachment_id',--}}
      {{--        'textField' => 'none',--}}
      {{--        'defaultTextField' => __('collaborators.valid'),--}}
      {{--      ])--}}
      {{--    </div>--}}

      {{--    <div class="w-full md:w-1/2 mt-2">--}}
      {{--      @include('pages.collaborators.collaborate.collaborator.partials.attachment-field', [--}}
      {{--        'label' => __('collaborators.term_dates'),--}}
      {{--        'type' => 'term-dates',--}}
      {{--        'attachmentField' => 'term_dates_attachment_id',--}}
      {{--        'textField' => 'none',--}}
      {{--        'defaultTextField' => __('collaborators.valid'),--}}
      {{--      ])--}}
      {{--    </div>--}}
      {{--      @endif--}}

    @endcan
  </div>
</div>
