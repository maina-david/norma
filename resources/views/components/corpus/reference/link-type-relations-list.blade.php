@props(['group', 'type', 'key', 'expression', 'canDelete', 'reference'])

<div class="mt-2 space-y-2 pl-4 w-full overflow-hidden text-sm" x-show="{{ $type }}_{{ $key }}">
  @foreach($group as $ref)
    <div class="flex">
      @if($ref->work_id === $expression->work_id)
        <a class="flex flex-col text-primary overflow-hidden flex-grow" href="{{ route('collaborate.work-expressions.references.activate', ['expression' => $expression->id, 'reference' => $ref->id, 'page' => request('page', 1)]) }}">
          @else
            {{--              <a class="flex flex-col text-primary overflow-hidden flex-grow" href="{{ route('collaborate.work-expressions.references.activate', ['reference' => $ref->id]) }}">--}}
          @endif
          <span class="text-ellipsis whitespace-nowrap overflow-hidden">{{ $ref->refPlainText?->plain_text }}</span>
          <span class="text-xs text-ellipsis whitespace-nowrap overflow-hidden text-primary-lighter">{{ $ref->work->title ?? '-' }}</span>
        </a>

        @if($canDelete)
          @can("collaborate.corpus.reference.link.link_type_{$key}")
            <x-ui.form
                class="mr-2"
                method="DELETE"
                action="{{ route('collaborate.work-expressions.references.'. $type .'.delete', ['expression' => $expression->id, 'reference' => $reference, 'related' => $ref->id]) }}"
            >
              <x-ui.button type="submit" theme="negative" styling="outline">
                <x-ui.icon name="unlink" size="4" />
              </x-ui.button>
            </x-ui.form>
          @endcan
        @endif
    </div>
  @endforeach
</div>
