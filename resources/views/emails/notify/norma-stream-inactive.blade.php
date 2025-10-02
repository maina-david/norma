@component('mail::message')
<div class="container">
<div class="row">
<div class="col-md-8 col-md-offset-2">
<div class="panel-default">
<div class="panel-heading" style="font-weight: bold">
  {{ __('mail.hello') }},
</div>
<br>
<div class="panel-body">
<div>{!! __('notify.norma_stream_deactivated.info', ['route' => route('my.settings.normas.show', ['norma' => $norma]), 'stream' => $norma->title]) !!}</div>
<br>
<div>
<strong>{{ __('notify.legal_update.jurisdiction') }}:</strong>
@if(isset($norma->location))
<ul>
<li>{{ $norma->location->title }}</li>
@else
<li>-</li>
</ul>
@endif
</div>
<div><strong>{{ __('notify.legal_update.categories') }}:</strong>
@foreach($norma->legalDomains as $category)
<ul>
<li>{{ $category->title }}</li>
</ul>
@endforeach
</div>
<div>{{ __('notify.norma_stream_deactivated.footer') }}</div>
</div>
</div>
</div>
</div>
@endcomponent
