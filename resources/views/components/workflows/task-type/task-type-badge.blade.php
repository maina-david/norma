@props(['type', 'textSize' => 'text-xs'])

@if($type)
  <div {{ $attributes->merge(['class' => 'inline-flex rounded-full items-center py-0.5 px-3 font-medium whitespace-nowrap text-white ' . $textSize]) }} style="background-color:{{ $type->colour }};">
    {{ $type->name }}
  </div>
@endif
