@component('mail::layout')
  {{-- Header --}}
  @slot('header')
    @component('mail::onboard_header', [
      'url' => !empty($baseClientUrl) ? $baseClientUrl : config('app.url_client'),
      'baseClientUrl' => !empty($baseClientUrl) ? $baseClientUrl : null,
      'whitelabel' => !empty($whitelabel) ? $whitelabel : null,
      'altHeader' => !empty($altHeader) ? $altHeader : false,
      ])
      {{ config('app.name') }}
    @endcomponent
  @endslot

  {{-- Body --}}
  {{ $slot }}

  {{-- Subcopy --}}
  @if (isset($subcopy))
    @slot('subcopy')
      @component('mail::subcopy')
        {!! $subcopy !!}
      @endcomponent
    @endslot
  @endif

  {{-- Footer --}}
  @slot('footer')
    @component('mail::onboard-footer', [
      'whitelabel' => !empty($whitelabel) ? $whitelabel : null,
      'baseClientUrl' => !empty($baseClientUrl) ? $baseClientUrl : null,
      ])
      <br>
      <p>&copy; {{ date('Y') }} {{ config('app.name') }} Limited.</p>
      <br>
      <p>{{ __('mail.address') }}</p>
      <br>
      @if (isset($unsubscribe))
        {!! $unsubscribe !!}
      @endif
    @endcomponent

    @component('mail::disclaimer')
      {{ __('mail.disclaimer_part_1') }}
    @endcomponent
    @component('mail::disclaimer')
      {{ __('mail.disclaimer_part_2') }}
    @endcomponent
  @endslot
@endcomponent
