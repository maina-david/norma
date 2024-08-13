@php
use App\Enums\Assess\ResponseStatus;
@endphp

@foreach (ResponseStatus::options() as $status)
  <turbo-stream action="update"
                target="assessment-item-response-answer-{{ $response->id }}-{{ $status }}">
    <template>
      @include('partials.assess.my.assessment-item-response.response-answer', ['forAnswer' => $status,
      'response' => $response, 'checked' => $response->answer === $status, 'markUnchanged' => false])
    </template>
  </turbo-stream>
@endforeach

<turbo-stream action="update"
              target="assessment-item-response-answer-{{ $response->id }}-unchanged">
  <template>
    @include('partials.assess.my.assessment-item-response.response-answer', ['forAnswer' => 'unchanged',
    'response' => $response, 'markUnchanged' => true])
  </template>
</turbo-stream>

<turbo-stream action="update"
              target="assessment-item-response-last-answered-{{ $response->id }}-at">
  <template>
    @include('partials.assess.my.assessment-item-response.last-answered', ['answeredAt' => $response->answered_at,
    'response' => $response])
  </template>
</turbo-stream>


<turbo-stream action="update"
              target="assessment-item-response-last-answered-{{ $response->id }}-by">
  <template>
    @include('partials.assess.my.assessment-item-response.last-answered', ['user' => $response->lastAnsweredBy,
    'response' => $response])
  </template>
</turbo-stream>


<turbo-stream action="replace" target="activities-for-assessment-item-response-{{ $response->id }}">

  <template>
    <turbo-frame
      loading="lazy"
      src="{{ route('my.assess.assessment-activities.index.for.response', ['response' => $response->id]) }}"
      id="activities-for-assessment-item-response-{{ $response->id }}"
    >
      <x-ui.skeleton />
    </turbo-frame>
  </template>
</turbo-stream>

<turbo-stream action="update" target="response_{{ $response->hash_id }}_table">

  <template>
    @include('partials.assess.my.assessment-item-response.detail-table')
  </template>
</turbo-stream>


<turbo-stream action="replace" target="response_{{ $response->hash_id }}_actions">
  <template>
  </template>
</turbo-stream>
