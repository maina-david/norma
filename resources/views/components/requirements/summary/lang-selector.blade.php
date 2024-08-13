@props(['original' => ' '])
@php
$languages = [
    '' => __('requirements.summary.translate_to'),
    $original => 'Original',
    'af' => 'Afrikaans',
    'zh' => 'Chinese (Simplified)',
    'da' => 'Danish',
    'nl' => 'Dutch',
    'en' => 'English',
    'fr' => 'French',
    'de' => 'German',
    'hi' => 'Hindi',
    'it' => 'Italian',
    'ja' => 'Japanese',
    'ko' => 'Korean',
    'pt' => 'Portuguese',
    'ru' => 'Russian',
    'es' => 'Spanish',
    'sw' => 'Swahili',
    'sv' => 'Swedish',
    'xh' => 'Xhosa',
    'zu' => 'Zulu',
    'po' => 'Polish',
];
@endphp

<x-ui.input
  name="language"
  type="select"
  :options="$languages"
  :placeholder="__('requirements.summary.translate_to')"
  {{ $attributes }}
/>
