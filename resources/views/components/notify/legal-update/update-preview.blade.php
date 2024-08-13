<div>
  @if ($docPreview)
    <x-corpus.doc.doc-preview :doc="$legalUpdate->createdFromDoc" />
  @else
    <turbo-frame id="content-full-text-{{ $legalUpdate->id }}"
                 src="{{ route('my.content-resources.show', [
                     'resource' => $legalUpdate->contentResource->id,
                     'targetId' => $legalUpdate->id,
                 ]) }}">
    </turbo-frame>
  @endif
</div>
