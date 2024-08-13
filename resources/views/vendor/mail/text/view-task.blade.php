{{ __('tasks.click_view_in_app') }}

{{ __('tasks.view_task') }}:
{{ ($baseClientUrl ?? config('app.url')) . '/libryos/activate/' . $libryoId . '/?redirect=/tasks/' . $id }}

{{$slot}}