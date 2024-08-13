<div class="space-y-4">
  <div class="font-semibold">{!! __('collaborators.quiz_content.title') !!}</div>

  <div>{!! __('collaborators.quiz_content.write_summary') !!}</div>

  <div class="font-semibold">{!! __('collaborators.guidelines') !!}</div>

  <div>
    <ul class="list-disc list-outside ml-8">
      <li>{!! __('collaborators.quiz_content.guideline_1') !!}</li>
      <li>{!! __('collaborators.quiz_content.guideline_2') !!}</li>
      <li>{!! __('collaborators.quiz_content.guideline_3') !!}</li>
    </ul>
  </div>

  <div class="font-semibold">{!! __('collaborators.legal_text') !!}</div>

  <div>
    <ul class="list-decimal list-outside ml-8 space-y-4">
      <li>
        <span>{!! __('collaborators.quiz_content.legal_text.item_1') !!}</span>

        <ul class="list-lower-alpha list-outside ml-8">
          <li>{!! __('collaborators.quiz_content.legal_text.item_1a') !!}</li>
          <li>{!! __('collaborators.quiz_content.legal_text.item_1b') !!}</li>
        </ul>
      </li>
      <li>{!! __('collaborators.quiz_content.legal_text.item_2') !!}</li>
      <li>{!! __('collaborators.quiz_content.legal_text.item_3') !!}</li>
    </ul>
  </div>

  <div>
    <x-ui.textarea name="quiz" wysiwyg="basic" />
  </div>
</div>
