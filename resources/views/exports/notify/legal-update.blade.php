@foreach ($notifications as $key => $notification)
  @component('mail::notification-table', ['notification' => $notification, 'baseClientUrl' => $baseClientUrl, 'appName' => $appName, 'index' => $key + 1])
@endcomponent
