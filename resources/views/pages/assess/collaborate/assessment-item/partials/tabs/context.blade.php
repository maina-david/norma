<div>
  @can('collaborate.assess.assessment-item.attach-context-questions')
    <x-ui.modal>
      <x-slot name="trigger">
        <div class="flex justify-end mt-4">
          <x-ui.button @click="open = true" theme="primary">{{ __('actions.add') }}</x-ui.button>
        </div>
      </x-slot>

      <div class="max-w-2xl w-screen">
        <div class="text-lg">{{ __('assess.assessment_item.attach_context_questions') }}</div>
        <x-ui.form method="POST"
                   :action="route('collaborate.assessment-item.context-questions.store', ['item' => $resource->id])">


          <x-compilation.context-question.context-question-selector
                                                                    name="context_questions[]"
                                                                    multiple
                                                                    :route="route('collaborate.context-questions.json.index')" />

          <div class="flex justify-end w-full mt-4">
            <div></div>
            <div>
              <x-ui.button class="mr-4" @click="open = false">{{ __('actions.close') }}</x-ui.button>
              <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
            </div>
          </div>


        </x-ui.form>

      </div>
    </x-ui.modal>
  @endcan

  <div>
    <table class="w-full">
      <tbody>
        @if ($resource->contextQuestions->count() < 1)
          <tr>
            <x-ui.td>
              <div class="text-center">
                {{ __('interface.no_data') }}
              </div>
            </x-ui.td>
          </tr>
        @endif

        @foreach ($resource->contextQuestions->sortBy('question') as $question)
          <x-ui.tr :loop="$loop">

            <x-ui.td>
              {{ $question->question }}
            </x-ui.td>

            <td>
              <x-ui.table-action-buttons with-delete
                                         base-route="collaborate.assessment-item.context-questions"
                                         base-permission="collaborate.assess.assessment-item"
                                         deletePermissionSuffix="detach-context-questions"
                                         :resource-id="['item' => $resource->id, 'question' => $question->id]" />
            </td>

          </x-ui.tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
