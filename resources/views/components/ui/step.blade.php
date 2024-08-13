<div
     class="{{ $active ? 'text-primary flex ' : 'hidden ' }}step grow md:flex flex-col justify-center items-center max-w-sm">

  <div class="step-number w-full flex justify-center items-center">
    <div
         class="{{ $active ? 'border-primary ' : '' }}shrink-0 rounded-full border-2 p-2 h-10 w-10 font-semibold text-lg flex items-center justify-center">
      <span>{{ $number }}</span>
    </div>
  </div>

  <div class="font-semibold mt-1 px-2">{!! $name !!}</div>

  @if ($description)
    <div class="text-sm px-2">{{ $description }}</div>
  @endif

</div>
