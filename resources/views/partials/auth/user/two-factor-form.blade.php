
@if(!$resource->hasEnabledTwoFactorAuthentication())
  @include('partials.auth.user.two-factor-disabled')
@else
  @include('partials.auth.user.two-factor-enabled')
@endif

