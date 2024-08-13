@component('mail::collaborate-message')
<div class="container">
<div class="row">
<div class="col-md-8 col-md-offset-2">
<div class="panel-default">
<div class="panel-heading" style="font-weight: bold">
  {{ __('mail.dear') . ' ' . $user->getFullNameAttribute() }},
</div>
<br>
<div class="panel-body">
<p>{{ __('collaborators.unsubscribe_communication.body') }}</p>
</div>
</div>
</div>
</div>
@endcomponent
