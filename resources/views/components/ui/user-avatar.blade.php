<div class="ring-white ring-1 h-{{ $dimensions }} w-{{ $dimensions }} rounded-full tippy">
  <img class="h-{{ $dimensions }} w-{{ $dimensions }} rounded-full tippy object-cover object-center"
       data-tippy-content="{{ $user->full_name }}"
       data-tippy-delay="300"
       data-tippy-animation="scale-subtle"
       src="{{ $user->profile_photo_url }}{{ str_contains($user->profile_photo_url, 'ui-avatars.com') ? '' : ($dimensions > 20 ? '/medium' : '/small') }}" alt="{{ $user->fname }}">

</div>
