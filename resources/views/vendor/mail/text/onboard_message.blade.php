@component('mail::layout')
  {{-- Header --}}
  @slot('header')
    @component('mail::header', ['url' => config('app.url')])
      {{ config('app.name') }}
    @endcomponent
  @endslot

  {{-- Body --}}
  {{ $slot }}

  {{-- Subcopy --}}
  @isset($subcopy)
    @slot('subcopy')
      @component('mail::subcopy')
        {{ $subcopy }}
      @endcomponent
    @endslot
  @endisset

  {{-- Footer --}}
  @slot('footer')
    @component('mail::disclaimer')
      {{ __('mail.disclaimer_part_1') }}
    @endcomponent
    @component('mail::disclaimer')
      {{ __('mail.disclaimer_part_2') }}
    @endcomponent
    @component('mail::footer')
      © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
    @endcomponent
  @endslot
@endcomponent
