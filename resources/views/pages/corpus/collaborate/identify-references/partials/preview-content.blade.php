<div>
  <div class="text-norma-gray-500 text-lg  mb-2">
    {{ __('interface.title') }}
  </div>
  <div class="text-base font-semibold leading-6 text-norma-gray-900 mb-10">
    {{ $title }}
  </div>
  <div class="text-norma-gray-500 text-lg mb-2">
    {{ __('corpus.reference.content_preview') }}
  </div>
  <div class="norma-legislation p-5 border border-norma-gray-100 rounded shadow">
    {!! $htmlContent !!}
  </div>
</div>
