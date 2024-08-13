@props(['tags'])

@foreach ($tags as $tag)
  <a data-turbo-frame="_top" href="{{ route('my.corpus.requirements.index', ['tags[]' => $tag->id]) }}"
     class="inline-block rounded-full text-white bg-secondary mr-2 px-3 py-0 mb-1">
    {{ $tag->title }}
  </a>
@endforeach
