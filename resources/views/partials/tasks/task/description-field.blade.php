<div
    class="mt-1 text-sm font-normal wysiwyg-content"
    x-data="{ isLarge: false, open: false }"
    x-init="isLarge = $el.clientHeight > 80"
>

  <div x-bind:style="isLarge && !open ? 'height:80px;overflow-y:hidden;' : ''">
    {!! $task->description ?? '-' !!}
  </div>

  <div class="flex">
    <div @click.stop="open = !open" class="text-primary font-semibold cursor-pointer mt-2" x-show="isLarge">
      <span x-show="open">{{ __('actions.see_less') }}</span>
      <span x-show="!open">{{ __('actions.see_more') }}</span>
    </div>
  </div>
</div>
