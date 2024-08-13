<div class="flex my-12">
  <div class="flex w-full items-start">
    <div class="w-1/2 mr-5">
      <div class="font-semibold">{{ __('auth.user.browser_sessions') }}</div>
      <p class="text-sm">{{ __('auth.user.browser_session_title') }}</p>
    </div>
    <div class="w-1/2">
      @include('partials.auth.user.logout-other-browser-sessions-form')
    </div>
  </div>
</div>
