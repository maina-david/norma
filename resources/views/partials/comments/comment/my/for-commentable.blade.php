@php
  use App\Services\Customer\ActiveNormasManager;
  $singleMode = app(ActiveNormasManager::class)->isSingleMode();
@endphp
<div></div>
<div>
  <div>
    <x-comments.post-comment :commentable-type="$commentableType" :commentable-id="$commentableId" :norma-id="$norma->id ?? null" :redirect="$redirect ?? null">
      @if(!$singleMode)
        <x-slot:postText>
          <div class="flex-grow flex items-center justify-end">
            <div>
              {{ __('comments.post_to_all_streams') }}
            </div>
          </div>
        </x-slot:postText>
      @endif
    </x-comments.post-comment>
  </div>
  <div></div>
</div>
<div class="mt-8">
  @foreach ($comments as $comment)
    <div class="my-1">
      <x-comments.comment :comment="$comment" :reply="$commentableType === 'comment'" :user="$user" :redirect="$redirect ?? null" />
    </div>
  @endforeach

  {{-- Don't show pagination for replies --}}
  @if ($commentableType !== 'comment')
    <div class="mt-5">
      {{ $comments->links() }}
    </div>
  @endif

</div>

</div>
