@component('mail::collaborate-message')
  <div class="container">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <div class="panel-default">
          <div class="panel-body">
            <div>
              {{ __('collaborators.team.team_details_updated', ['team' => $team->title]) }}
            </div>
            <ul>
              @foreach ($dirty as $field)
                <li>{{ $field }}</li>
              @endforeach
            </ul>
          </div>
        </div>
      </div>
    </div>
  @endcomponent
