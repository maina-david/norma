@foreach ($reference->raisesConsequenceGroups as $consequenceGroup)
  <div class="my-2">

{{--    <div class="divide-y divide-norma-gray-50">--}}
{{--      @foreach ($consequenceGroup->consequences as $consequence)--}}
{{--        <div class="p-3">--}}
{{--          {{ $consequence->toReadableString() }}--}}
{{--        </div>--}}
{{--      @endforeach--}}
{{--    </div>--}}
    <div class="">
      <x-ui.collapse :title="$consequenceGroup->refPlainText?->plain_text" icon="exclamation-circle">
        <div class="norma-legislation p-3">
          {!! $consequenceGroup->htmlContent?->cached_content !!}
        </div>
      </x-ui.collapse>
    </div>
  </div>
@endforeach
