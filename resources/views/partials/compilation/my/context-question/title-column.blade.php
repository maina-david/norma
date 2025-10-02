@php use App\Cache\Ontology\Collaborate\CategorySelectorCache; @endphp
@php
    $categories = CategorySelectorCache::updateLabel($question->categories, 'display_label', '/');
@endphp
<div>
  <x-ui.link
    href="{{ route('my.context-questions.show', ['question' => $question->hash_id]) }}"
    data-turbo-frame="_top"
    class="{{ empty($question->mainDescription->description ?? '') ? '' : 'tippy' }}"
    data-tippy-content="{{ strip_tags(html_entity_decode($question->mainDescription->description ?? '')) }}"
  >
    {{ $question->toQuestion() }}
  </x-ui.link>

  <div class="text-xs text-norma-gray-500 flex flex-col items-start">
    @foreach($categories as $cat)
      <div
        class="tippy"
        data-tippy-content="{{ $cat->display_label }}"
      >
        {{ $cat->getOriginal('display_label') }}
      </div>
    @endforeach
  </div>
</div>
