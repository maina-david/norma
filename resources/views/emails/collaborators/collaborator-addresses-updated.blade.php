@component('mail::collaborate-message')
<div class="container">
<div class="row">
<div class="col-md-8 col-md-offset-2">
<div class="panel-default">
<div class="panel-body">
<p>
{{ __('collaborators.update_addresses.body', ['user' => $user->full_name]) }}
</p>
<br>

@component('mail::button', ['color' => 'green', 'url' => route('collaborate.collaborators.show', ['collaborator' => $user->id, 'tab' => 'profile']) ])
   {{ __('notifications.collaborate.have_a_look') }}
@endcomponent

</div>
</div>
</div>
</div>
@endcomponent
