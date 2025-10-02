<p style="text-align: center; font-size: 14px; line-height: 26px; color: #1a2434">{{ __('tasks.click_view_in_app') }}</p>

@component('mail::button', ['color' => 'green', 'url' => ($baseClientUrl ?? config('app.url')) . '/normas/activate/' . $normaId . '/?redirect=/tasks/' . $id ])
{{ __('tasks.view_task') }}
@endcomponent
