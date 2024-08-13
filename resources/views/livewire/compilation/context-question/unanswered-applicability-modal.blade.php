<div>
@if(!$hidden)
  <x-ui.modal
      :closable="false"
      x-init="function () {
    if (document.cookie.split(';').filter(function (i) { return i.trim() === 'applicability_dismissed=1'; }).length < 1) {
      this.open = true;
    }
  }"
  >
    <x-slot:trigger>
      <div></div>
    </x-slot:trigger>

    <div class="max-w-md text-sm px-2">
      <div class="mb-4 font-semibold">
        {{ __('compilation.context_question.pending_applicability_modal.title', ['number' => $pendingCount]) }}
      </div>

      <div class="text-libryo-gray-600 space-y-2">
        <p>
          {!! __('compilation.context_question.pending_applicability_modal.paragraph_1', ['number' => $pendingCount]) !!}
        </p>
        <p>
          {!! __('compilation.context_question.pending_applicability_modal.paragraph_2') !!}
        </p>
        <p>
          {!! __('compilation.context_question.pending_applicability_modal.paragraph_3') !!}
        </p>
      </div>

      <div class="mt-2 border-t border-libryo-gray-200 pt-4">
        <x-ui.input :label="__('actions.dont_show_again')" name="hide_forever" type="checkbox" wire:change="manageSetting($event.target.checked)" />
      </div>
      <div class="flex justify-end items-center mt-2 pt-4">
        <x-ui.button type="button" styling="outline" theme="primary" @click="document.cookie = 'applicability_dismissed=1; max-age=' + 7*24*60*60;open = false;">
          {{ __('compilation.context_question.pending_applicability_modal.later') }}
        </x-ui.button>

        <x-ui.button type="link" styling="outline" theme="primary" class="ml-2" href="{{ $target }}">
          {{ __('compilation.context_question.pending_applicability_modal.answer') }}
        </x-ui.button>
      </div>
    </div>
  </x-ui.modal>
@endif
</div>

