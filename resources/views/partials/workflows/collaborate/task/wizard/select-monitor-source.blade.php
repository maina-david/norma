<div>

  <x-arachno.source.source-selector value="{{ old('source_id') }}" true />

  <div class="px-2 mt-4 font-semibold">
    @error('source_id')
    <div class="text-sm text-red-400">{!! $message !!}</div>
    @enderror
  </div>
</div>
