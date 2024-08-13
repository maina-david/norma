<x-layouts.guest-plain>
  <script src="https://html2canvas.hertzen.com/dist/html2canvas.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

  <header>
    <div class="py-4  sm:px-6 flex flex-col md:flex-row justify-between items-center space-x-2 space-y-4">
      <div class="font-light text-lg md:text-2xl text-libryo-gray-900">
        <span>
          <span>{{ __('my.nav.requirements') }}</span>
          <span class="text-xs text-libryo-gray-500 italic ml-3">{{ $libryo->title ?? ($organisation->title ?? '') }}</span>
        </span>
      </div>
    </div>
  </header>

  @if ($libryo->compilation_in_progress ?? false)
    <div class="text-white bg-secondary p-5 my-10">
      {{ __('customer.libryo.compilation_in_progress_info') }}
    </div>
  @endif

  <div>
    {!! $content !!}
  </div>
</x-layouts.guest-plain>
