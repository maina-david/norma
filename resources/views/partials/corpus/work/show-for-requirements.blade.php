<div>
  <turbo-frame loading="lazy"
               src="{{ route('my.references.for.requirements', ['work' => $work->id]) }}"
               id="work-requirements-or-full-text-{{ $work->id }}">

  </turbo-frame>
</div>
