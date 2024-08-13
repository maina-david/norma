@php
  $title = $work->title;
  if($work->title_translation) {
      $title .= " [{$work->title_translation}]";
  }
@endphp

<span
    @click="$dispatch('selected', { id: {{ $work->id }}, title: '{{ str_replace('\'', '\\\'', preg_replace('/\s+/', ' ', $title)) }}' }); open = false;"
    class="text-primary cursor-pointer"
>
  {{ $title }}
</span>
