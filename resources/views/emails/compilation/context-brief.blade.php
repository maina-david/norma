@component('mail::message')
<div class=”container”>
<div class=”row”>
<div class=”col-md-8 col-md-offset-2">
<div class=”panel panel-default”>
<div class=”panel-heading” style="font-weight: bold">{{ $organisation->title }}
</div>
<br>
<br>
<div class=”panel-body”>
Your context brief is attached.
</div>
</div>
</div>
</div>
</div>
@endcomponent
