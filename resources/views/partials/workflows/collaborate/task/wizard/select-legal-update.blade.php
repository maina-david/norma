@php use App\Models\Notify\LegalUpdate; @endphp
<div>
  @if($update = old('legal_update_id'))
    <div class="font-semibold text-lg">
      <input type="hidden" name="from_form" value="1">
      <input type="hidden" name="legal_update_id" value="{{ $update }}">
    </div>

    @include('partials.notify.collaborate.legal-update.form', ['noActions' => true, 'noUpload' => true])
  @else
    <div>
      <span>{!! __('notify.legal_update.select_from_doc', ['target' => route('collaborate.corpus.docs.for-update.index')]) !!}</span>
    </div>
  @endif

  <div class="px-2 mt-4 font-semibold">
    @error('legal_update_id')
    <div class="text-sm text-red-400">{!! $message !!}</div>
    @enderror
  </div>
</div>
