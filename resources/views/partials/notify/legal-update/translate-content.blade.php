
<div class="text-lg mb-3">{{ __('notify.legal_update.highlights') }}</div>

<div class="norma-summary text-sm">
  {!! $update->highlights ?? '' !!}
</div>

<div class="my-5">
  <a
    target="_top"
    href="{{ route('my.notify.legal-updates.preview', ['update' => $update->id]) }}"
    class="text-primary"
  >
    {{ __('notify.legal_update.full_text_link') }}
  </a>
</div>

<div>
  <div class="text-lg mb-3">{{ __('notify.legal_update.changes_to_requirements') }}</div>
  <div class="norma-summary text-sm">
    {!! $update->changes_to_register ?? '' !!}
  </div>
</div>

  @if($update->late)
    <div>
      <div class="norma-summary text-sm mt-14 italic">
        {!! __('notifications.collaborate.update_is_late_wording') !!}
      </div>
    </div>
  @endif
