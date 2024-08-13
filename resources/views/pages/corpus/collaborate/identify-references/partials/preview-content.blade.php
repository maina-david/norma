<div>
  <div class="text-libryo-gray-500 text-lg  mb-2">
    {{ __('interface.title') }}
  </div>
  <div class="text-base font-semibold leading-6 text-libryo-gray-900 mb-10">
    {{ $title }}
  </div>
  <div class="text-libryo-gray-500 text-lg mb-2">
    {{ __('corpus.reference.content_preview') }}
  </div>
  <div class="libryo-legislation p-5 border border-libryo-gray-100 rounded shadow">
    {!! $htmlContent !!}
  </div>
</div>
