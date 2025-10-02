@php use App\Enums\Compilation\ApplicabilityNoteType; @endphp
<div class="h-full">
  <div wire:loading class="w-full">
    <x-ui.skeleton :rows="7" flat no-circle class="w-full"/>
  </div>

  <form
      wire:loading.remove class="h-full no-shadow flex flex-col overflow-hidden app-action-form"
      x-data="{
      showActions: false,
      validateActionVisibility() {
        this.showActions = document.querySelectorAll('.app-action-form input.app-action-selection:checked').length > 0;
      },
      triggerAction(el) {
        var type = el.getAttribute('value');
        document.querySelectorAll('.app-actions-list input[type=checkbox]').forEach(function (item){ item.checked = false; });
        document.querySelectorAll('.app-actions-list input[type=checkbox][value=' + type + ']').forEach(function (item){ item.checked = true; });
        el.closest('form').requestSubmit()
      },
      handleBulkAction(action) {
        document.querySelector('#bulk_action').value = action;
        var data = new FormData(document.querySelector('.app-action-form'));
        var payload = {
          references: data.getAll('references[]'),
          action: data.get('bulk_action'),
          comment: data.get(action + 'bulk_comment'),
          type: data.get(action + 'bulk_type'),
        };

        if (!payload.type || !payload.comment || payload.comment.length < 1 || payload.type.length < 1) {
          return 'Please select an option and provide extra details.';
        }

        $wire.handleActions(payload);

        return null;
      },

    }"
  >
    <div class="flex-shrink-0 relative">
      <div class="absolute left-0 top-0 pl-2">
        <x-ui.dropdown class="w-56">
          <x-slot name="trigger">
            <x-ui.button x-show="showActions" class="mt-1" styling="outline" theme="primary">
              <x-ui.icon name="square-check"/>
              <x-ui.icon name="angle-down" class="ml-4"/>
            </x-ui.button>
          </x-slot>

          {{-- ACTIONS LIST --}}
          <div class="py-1 app-actions-list" role="none">
            <input type="hidden" id="bulk_action" name="bulk_action" value="add">

            <div class="my-2">
              <livewire:compilation.context-question.applicability-requirement-changer
                bulk="handleBulkAction('add')"
                prefix="add"
                :norma-ids="[]"
                :reference-ids="[]"
                :in-norma="false"
                :key="Str::random(64)"
              />
            </div>

            <div class="mb-2">
            <livewire:compilation.context-question.applicability-requirement-changer
                bulk="handleBulkAction('remove')"
                prefix="remove"
                :norma-ids="[]"
                :reference-ids="[]"
                :in-norma="true"
                :key="Str::random(64)"
              />
            </div>
          </div>
        </x-ui.dropdown>
      </div>

      <x-ui.table :rows="$works" no-search white-header no-margins not-rounded class="styled-app-table">
        <x-slot:head>
          <th class="w-10"></th>
          <x-ui.th class=""></x-ui.th>
          @if($norma)
            <x-ui.th class="text-center w-40">{{ __('compilation.context_question.recommended') }}</x-ui.th>
            <x-ui.th class="text-center w-40">{{ __('compilation.context_question.included_in_stream') }}</x-ui.th>
          @else
            <x-ui.th
                class="text-center w-40">{{ __('compilation.context_question.included_in_number_of_stream') }}</x-ui.th>
          @endif
        </x-slot:head>

        <x-slot:body>
        </x-slot:body>

        <x-slot:tfoot>
          <tfoot></tfoot>
        </x-slot:tfoot>
      </x-ui.table>
    </div>

    <div class="flex-grow overflow-y-auto relative">
      <x-ui.table :rows="$works" no-search white-header no-margins class="styled-app-table">
        <x-slot:body>
          @foreach($works as $work)
            @include('partials.compilation.my.context-question.applicability-work-item')
          @endforeach
        </x-slot:body>

        <x-slot:tfoot>
          <tfoot></tfoot>
        </x-slot:tfoot>
      </x-ui.table>
    </div>

    <div class="flex-shrink-0 mt-2">
      {{ $worksPaginator->withQueryString()->links() }}
    </div>
  </form>
</div>
