<x-customer.libryo.my.settings.compilation-layout :libryo="$libryo">
  <x-ui.form method="post"
             :action="route('my.settings.libryos.compilation-settings.update', ['libryo' => $libryo->id])">
    <div class="my-8">

      @foreach (['use_collections', 'use_legal_domains', 'include_no_legal_domains', 'use_context_questions', 'include_no_context_questions', 'use_topics', 'include_no_topics'] as $field)
        <div class="mt-3">
          <x-ui.input type="checkbox"
                      :value="old($field, $resource->{$field} ?? '')"
                      :name="$field"
                      label="{{ __('customer.compilation_setting.' . $field) }}" />
        </div>
      @endforeach


    </div>

    <x-slot name="footer">
      <div>
        <x-ui.button type="submit" theme="primary">{{ __('actions.update') }}</x-ui.button>
      </div>
    </x-slot>
  </x-ui.form>

</x-customer.libryo.my.settings.compilation-layout>
