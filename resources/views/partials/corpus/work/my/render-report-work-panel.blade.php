<x-corpus.work.work-panel :work="$work" :show-type="false" show-multiple-flags>
  <turbo-frame src="{{ route('my.requirements.with.relation.show', ['work' => $work->id]) }}"
               loading="lazy"
               id="work-with-relations-{{ $work->id }}">
    <x-ui.skeleton :rows="2" />
  </turbo-frame>
</x-corpus.work.work-panel>
