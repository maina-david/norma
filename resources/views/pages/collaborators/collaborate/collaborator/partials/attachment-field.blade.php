@php
use App\Enums\Collaborators\DocumentArchiveOptions;use App\Enums\Collaborators\DocumentType;
use App\Models\Collaborators\Collaborator;
use App\Models\Collaborators\ProfileDocument;
/** @var DocumentType $type */
/** @var Collaborator $resource */
/** @var ProfileDocument $document */
$inactiveDocuments = $resource->profile->relationLoaded('inactiveDocuments') ? $resource->profile->inactiveDocuments : collect();
$forApplication = $forApplication ?? false;
$personal = $personal ?? false;
$revalidate = $revalidate ?? false;
$document = $resource->profile->documents()->where('type', $type)->orderBy('id', 'desc')->first() ?? new ProfileDocument();
$document->id = $document->id ?? '';
$approved = (bool) $document->approved_at;
$expiredTrans = 'interface.' . ($document->expired ? 'expired_on' : 'expires_on');

$canUpload = $forApplication ? $type === DocumentType::CONTRACT : ($canUpload ?? false);
$canValidate = $canValidate ?? false;
$validationPartial = $validationPartial ?? false;

$textField = $approved ? '' : __('collaborators.waiting_for_validation');
$textClass = $approved ? ($document->expired ? 'text-negative' : 'text-primary') : 'text-secondary-darker font-semibold';

$params = $personal ? ['type' => $type->field()] : ['document' => $document->id];

$downloadRoute = $personal
  ? route('collaborate.profile.document.show', $params)
  : route("collaborate.profile.document.download", $params);

$streamRoute = $personal
  ? $downloadRoute
  : route("collaborate.profile.document.stream", $params);

if ($canUpload) {
    $uploadRoute = $personal
      ? route('collaborate.profile.document.update', $params)
      : route("collaborate.profile.document.upload", ['profile' => $resource->profile, 'type' => $type->field()]);
}

if ($type === DocumentType::CONTRACT && $personal) {
    $canUpload = false;
}

$needsReason = in_array($type, [
    DocumentType::IDENTIFICATION,
    DocumentType::VISA,
]);

$updateReasons = DocumentArchiveOptions::forSelector();
$required = [
    DocumentType::IDENTIFICATION,
    DocumentType::CONTRACT,
    DocumentType::TRANSCRIPT,
];

if (in_array($type, $required)) {
  $updateReasons = collect($updateReasons)->reject(fn ($item) => $item['value'] === DocumentArchiveOptions::NOT_REQUIRED->value)->toArray();
}

$previousDocs = $inactiveDocuments->where('type', $type);
@endphp

<x-ui.show-field :label="$label">
  <div class="space-y-2">

    <div>
      @if ($document->id)
        <x-slot name="labelSuffix">
          @if($document->attachment_id)
            <span class="flex space-x-2 items-center">
              <a target="_blank" href="{{ $downloadRoute }}" class="{{ $textClass }} ml-2">
                <x-ui.icon name="download" size="5"/>
              </a>

              <a class="{{ $textClass }} flex items-center space-x-2" target="_blank" href="{{ $document->attachment_id ? $streamRoute : '' }}">
                <x-ui.icon name="eye" size="5"/>
              </a>
            </span>
          @endif
        </x-slot>

        <div class="flex items-center space-x-2 {{ $textClass }}">
            @if($approved && !$document->valid_to)
              <span>
                {{ __('collaborators.valid') }}
              </span>
            @endif

            @if(!$approved || ($document->expired && $document->valid_to))
              <x-ui.icon name="exclamation-circle"/>
            @endif

            @if($textField || ($suffix ?? false))
              <span>{{ $textField ? "$textField." : '' }} {{ $suffix ?? '' }}</span>
            @endif

            @if ($approved && $document->valid_to)
              <span>{{ __($expiredTrans, ['date' => $document->valid_to->format('dS M Y')]) }}</span>
            @endif
        </div>
      @else
        <div>
          -
        </div>
      @endif
    </div>

    <div class="flex items-center space-x-4">

      @if((!$approved || $revalidate) && !$personal && !empty($document->id))
        @can('collaborate.collaborators.collaborator.validate-documents')
          @include('partials.collaborators.collaborate.collaborator.validate-document', ['document' => $document, 'subtypes' => $subtypes ?? null, 'extra' => $validationPartial ?? null])
        @endcan
      @endif

      @if($canUpload ?? false)
        <div x-data="{ upload: {{ json_encode($errors->has($type->field())) }} }">
          <x-ui.button styling="outline" @click="upload = !upload">
            <span class="mr-2">{{ __('actions.upload_new', ['name' => $label]) }}</span>
            <x-ui.icon x-show="!upload" name="angle-down" size="4"/>
            <x-ui.icon x-show="upload" name="angle-up" size="4"/>
          </x-ui.button>

          <div x-show="upload" class="w-96 max-w-screen-90 pt-4">
            <x-storage.collaborate.uploader method="PUT" :route="$uploadRoute" :name="$type->field()">
              @if(isset($uploadFields) || $needsReason)

                <x-slot:formBody>
                  @isset($uploadFields)
                    @include($uploadFields)
                  @endisset

                  @if($needsReason)
                    <x-ui.input
                        label="{{ __('collaborators.collaborator.reason_for_update') }}"
                        name="reason_for_update"
                        type="select"
                        required
                        :options="$updateReasons"
                    />
                  @endif
                </x-slot:formBody>

              @endif
            </x-storage.collaborate.uploader>
          </div>
        </div>
      @endif

    </div>
  </div>

  @if(!$personal && $previousDocs->isNotEmpty())
    <div x-data="{ open: false }">
      <div class="mt-4 mb-2">
        <x-ui.button theme="gray" styling="outline" @click="open = !open">
          <span class="mr-4">{{ __('collaborators.previous_documents') }}</span>
          <x-ui.icon size="4" x-show="!open" name="angle-right" />
          <x-ui.icon size="4" x-show="open" name="angle-down" />
        </x-ui.button>
      </div>

      <div x-show="open" class="space-y-1">
        @foreach($previousDocs as $doc)
          <div class="flex items-center py-3 px-4 bg-libryo-gray-100 rounded-lg">
            <div class="flex-grow">
              {{ $doc->created_at->format('dS F Y') }}
            </div>

            @if($doc->attachment_id)
              <div class="flex space-x-4 items-center flex-shrink-0">
                <a target="_blank" href="{{ route("collaborate.profile.document.download", ['document' => $doc->id]) }}" class="{{ $textClass }} ml-2">
                  <x-ui.icon name="download" size="5"/>
                </a>

                <a class="{{ $textClass }} flex items-center space-x-2" target="_blank" href="{{ route("collaborate.profile.document.stream", ['document' => $doc->id]) }}">
                  <x-ui.icon name="eye" size="5"/>
                </a>
              </div>
            @endif
          </div>
        @endforeach
      </div>
    </div>
  @endif
</x-ui.show-field>
