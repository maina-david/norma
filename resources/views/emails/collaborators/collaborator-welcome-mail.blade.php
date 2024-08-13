@component('mail::collaborate-message')
<div class="container">
<div class="row">
<div class="col-md-8 col-md-offset-2">
<div class="panel-default">
<div class="panel-heading" style="font-weight: bold">
  {{ __('mail.hi') . ' ' . $collaborator->fname }},
</div>
<br>
<div class="panel-body">
<p>{{ __('mail.librarian_platform_access') }}</p>
<p>{!! __('mail.to_get_started', ['url' => str_replace('my.', 'collaborate.', route('password.request'))]) !!}</p>
<ul>
  <li>{{ __('mail.update_your_information') }}</li>
  @if($collaborator->profile->is_team_admin)
    <li>{{ __('mail.add_bank_details') }}</li>
  @endif
</ul>
<p>{{ __('mail.apply_for_task') }}</p>
<p>{{ __('mail.thanks_again') }}</p>
<p>{{ __('mail.hope_you_enjoy') }}</p>

</div>
</div>
</div>
</div>
@endcomponent
