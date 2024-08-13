@php
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
@endphp
@if (preg_match('~MSIE|Internet Explorer~i', $userAgent) || preg_match('~Trident/7.0(.*)?; rv:11.0~', $userAgent))
  <div class="h-44 bg-negative text-white absolute z-50 top-0 p-5">
    This notice serves to let you know since Microsoft plans to no longer support IE, neither do we.
    Please make use of a different web browser to improve your experience on our platform.
    Feel free to contact us if you have any questions.
  </div>
@endif
