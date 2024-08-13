@props(['name', 'value', 'label' => '', 'multiple' => false])

<x-ui.input
  {{ $attributes }}
  type="select"
  :value="$value"
  :name="$name"
  :label="$label"
  :options="['' => __('corpus.work.work_status.all')] + \App\Enums\Corpus\WorkType::lang()"
  :multiple="$multiple"
  @change="$dispatch('changed', $el.value)"
/>
