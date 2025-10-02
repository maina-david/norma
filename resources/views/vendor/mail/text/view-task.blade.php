{{ __('tasks.click_view_in_app') }}

{{ __('tasks.view_task') }}:
{{ ($baseClientUrl ?? config('app.url')) . '/normas/activate/' . $normaId . '/?redirect=/tasks/' . $id }}

{{$slot}}