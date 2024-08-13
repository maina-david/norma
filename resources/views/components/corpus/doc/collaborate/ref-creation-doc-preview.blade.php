<div class="h-full">
  @include('partials.corpus.doc.full-text', [
      'resourceLink' => $resourceLink,
      'tocRoute' => route('collaborate.docs.identify-toc', [
          'expression' => $expression,
          'doc' => $doc->id,
          'show' => 1,
      ]),
  ])
</div>
