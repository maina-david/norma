@component('mail::collaborate-message')
    <div class="container">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel-default">
                    <div class="panel-body">
                        <div>
                            There were some missing Action Areas in your import:
                        </div>
                        <ul>
                            @foreach ($missingAreas as $area)
                                <li>Subject: {{ $area['subject'] ?? '' }}, Control: {{ $area['control'] ?? '' }} (for
                                    {{ $area['reference'] }})</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endcomponent
