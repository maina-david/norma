@php
  use App\Enums\Workflows\WizardStep;
  $firstStep = $handover->board->wizard_steps[0] ?? null;
  $createPayload = [];
  $canCreate = false;

  if ($firstStep === WizardStep::CREATE_WORK->value && !$legislation->work_id) {
      $canCreate = true;
      $createPayload = [
        'title' => $legislation->title,
        'title_translation' => $legislation->title_translation,
        'source_url' => $legislation->source_url,
        'source_id' => $resource->source_id,
      ];
  }

  if ($firstStep === WizardStep::SELECT_WORK->value && $legislation->work && $legislation->work->catalogueDoc && $legislation->work->catalogueDoc->id == $legislation->catalogue_doc_id) {
      $canCreate = true;
      $createPayload = [
        'work_id' => $legislation->work_id,
      ];
  }

  $createPayload['redirect_back'] = route('collaborate.docs-for-update.handover-updates.create', ['doc' => $legislation->doc_id, 'target' => $handover->pivot->id]);
  $createPayload['board'] = $handover->board_id;
  $createPayload['stage'] = 1;

@endphp

@if($canCreate)
<x-ui.modal>
  <x-slot:trigger>
    <x-ui.button @click="open = true" styling="outline" theme="primary">
      {{ __('corpus.doc.generate_workflow') }}
    </x-ui.button>
  </x-slot:trigger>

  <div>

    <div class="mb-4 font-semibold text-lg">
      {{ __('corpus.doc.generate_workflow') }}
    </div>

    <div class="mb-4">
      {{ __('corpus.doc.generate_workflow_confirmation') }}
    </div>


      <div class="flex items-center justify-end">
        <x-ui.button @click="open = false" styling="outline" type="button">
          {{ __('actions.cancel') }}
        </x-ui.button>

        <x-ui.button
            class="ml-4"
            styling="outline"
            theme="primary"
            type="link"
            href="{{ route('collaborate.tasks.wizard.create', $createPayload) }}"
        >
          {{ __('corpus.doc.generate_workflow') }}
        </x-ui.button>
      </div>
  </div>
</x-ui.modal>
@endif
