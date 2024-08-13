@props(['users', 'dimensions' => 8, 'limit' => 3])
@php
  $remainder = count($users) - $limit;
  $users = collect($users)->take($limit)->all();
@endphp
{{-- w-6 --}}

<div class="flex items-center">
  @foreach($users as $user)
    <div class="w-{{ $dimensions - 2 }}">
      <x-ui.user-avatar :user="$user" :dimensions="$dimensions" />
    </div>
  @endforeach

  @if($remainder > 0)
      <div class="text-white bg-libryo-gray-400 w-{{ $dimensions }} h-{{ $dimensions }} rounded-full flex items-center justify-center ring-1 ring-white">
        <span>+{{ $remainder }}</span>
      </div>
  @endif
</div>
