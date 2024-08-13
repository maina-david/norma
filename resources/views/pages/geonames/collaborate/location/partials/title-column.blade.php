<article class="flex items-center">
  <div class="shrink-0 mr-4">
    <x-ui.country-flag
                       data-tippy-content="{{ $row->title }}"
                       data-tippy-delay="200"
                       data-tippy-animation="scale-subtle"
                       :countryCode="$row->flag"
                       class="h-8 w-8 tippy rounded-full" />
  </div>

  <section class="flex flex-col justify-center whitespace-nowrap">
    <div>
      @if ($row->title && $row->children_count > 0)
        <a href="{{ route('collaborate.jurisdictions.children', $row->id) }}" class="text-primary font-semibold">{{ $row->title }}</a>
      @else
        {{ $row->title ?? '-' }}
      @endif
    </div>
    <aside class="sub-row">
      <div>
        @if ($row->slug)
          <span>{{ $row->slug }}</span>
        @endif
      </div>
      <div>{{ $row->type->title ?? '-' }}</div>
    </aside>
  </section>
</article>
