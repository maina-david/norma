@props(['closable' => true, 'margin' => 'sm:my-8', 'teleport' => false, 'isOpen' => false])
<div
  {{ $attributes }}
  x-data="{
    open: {{ json_encode($isOpen) }},
    handleClose: function () {
      this.open = false;
      $dispatch('closed');
    }
  }"
  @if($closable)
    @keydown.window.escape="handleClose"
  @endif
>

  {{ $trigger }}

  @if($teleport)
    <template x-teleport="body">
  @endif
    <div
      x-show="open"
      class="fixed z-40 inset-0 h-screen overflow-y-auto"
      aria-labelledby="modal-title"
      role="dialog"
      aria-modal="true" style="display: none;"
    >
      <div class="flex items-center justify-center h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div
             x-show="open"
             @if($closable) @click="handleClose" @endif
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500 bg-opacity-75" aria-hidden="true"
        ></div>

        <span class="hidden sm:inline-block" aria-hidden="true">&#8203;</span>

        <div
             x-show="open"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="{{ $margin }} inline-block align-bottom bg-white rounded-lg px-4 pb-4 pt-7 text-left shadow-xl transform transition-all sm:align-middle mx-2 relative"
        >
          @if ($closable)
            <div @click="handleClose" class="absolute top-4 right-4 cursor-pointer">
              <x-ui.icon name="times" siye="4" class="text-norma-gray-600 hover:text-norma-gray-900" />
            </div>
          @endif
          <div class="whitespace-normal">
            {{ $slot }}
          </div>

          @if($footer ?? false)
          <div class="mt-10 sm:mt-8 flex flex-col sm:flex-row sm:space-x-4 space-y-3 sm:space-y-0 justify-between">
            {{ $footer ?? '' }}
          </div>
          @endif

        </div>
      </div>
    </div>
  @if($teleport)
    </template>
  @endif
</div>
