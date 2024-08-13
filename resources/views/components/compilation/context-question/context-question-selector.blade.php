@props([
  'question',
  'options' => [],
  'multiple' => false,
  'label' => null,
  'route' => route('collaborate.context-questions.json.index'),
  'name' => 'context_question_id',
  'dataLoadStartSearching' => 4,
  'placeholder' => __('nav.context_questions'),
  'required' => false
])

<x-ui.input
  :required="$required"
  :name="$name"
  :multiple="$multiple"
  label="{{ $label ?? __('compilation.context_question.index_title') }}"
  type="select"
  :options="isset($question) ? [$question->id => $question->question] : $options"
  class="remote-select"
  :data-load="$route"
  :data-load-start-searching="$dataLoadStartSearching"
  :placeholder="$placeholder"
  @change="$dispatch('changed', $el.value)"
  {{ $attributes }}
/>
