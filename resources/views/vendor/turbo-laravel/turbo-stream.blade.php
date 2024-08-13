<x-turbo::stream :target="$target ?? null" :action="$action" :targets="$targets ?? null" :merge-attrs="$attrs ?? []">
@if ($partial ?? false)
    @include($partial, $partialData)
@elseif ($content ?? false)
    {{ $content }}
@endif

@if(session()->has('flash.message'))
  @include('partials.ui.streamed-alert', ['type' => session()->pull('flash.type', 'success'), 'message' => session()->pull('flash.message')])
@endif
</x-turbo::stream>
