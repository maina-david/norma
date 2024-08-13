@include('partials.corpus.collaborate.work.form', [
  'resource' => new \App\Models\Corpus\Work(),
  'noActions' => true,
  'withSourceDocument' => true,
])
