<div x-data="{extended:false}">
  <div x-bind:class="extended ? '' : 'h-8 overflow-hidden text-ellipsis'">
    {{ $slot }}
  </div>

  @if(strlen($slot) > 100)
    <div class="mt-2">
      <a class="mt-2 text-primary" href="#" @click.prevent="extended = !extended">
        <span x-show="!extended">{{ __('interface.view_more') }}</span>
        <span x-show="extended">{{ __('interface.view_less') }}</span>
      </a>
    </div>
  @endif
</div>
