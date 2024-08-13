@include('partials.notify.collaborate.legal-update.form', [
  'resource' => new \App\Models\Notify\LegalUpdate(),
  'noActions' => true,
  'withSourceDocument' => true,
])
