@php use App\Cache\Ontology\Collaborate\CategorySelectorCache; @endphp
@props(['categories'])
@php
  $cached = app(CategorySelectorCache::class)->get();
@endphp

@foreach ($categories as $category)
  <a
      data-turbo-frame="_top"
      href="{{ route('my.corpus.requirements.index', ['topics[]' => $category->id]) }}"
      class="inline-block rounded-full text-white bg-secondary mr-2 px-3 py-0 mb-1"
  >
    {{ $cached[$category->id] ?? $category->display_label }}
  </a>
@endforeach
