@component('mail::collaborate-message')
  <div class="container">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <div class="panel panel-default">
          <div class="panel-heading" style="font-weight: bold">
            {{ __('mail.hi') }} {{ $user->fname }},
            <br>
            <br>
          </div>
          <div class="panel-body">
            {{ __('payments.mail.thanks_for_completing_tasks') }}
            <br>
            <br>
            {{ __('payments.mail.view_detailed_breakdown') }}
            <br>
            <br>
            {{ __('payments.mail.view_task_ratings') }}
            <br>
            <br>
            {!! __('payments.mail.give_feedback', ['url' => 'https://www.surveymonkey.com/r/DV35BZG']) !!}
            <br>
            <br>
            {{ __('payments.mail.many_thanks') }}
            <div style="font-weight: bold">{{ __('mail.norma') }}</div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endcomponent
