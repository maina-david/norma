<div>
  <div
    class="flex flex-col items-start"
    x-init="initFiltered"
    x-data="{
      isFiltered: function (event) {
        if (event.detail === false) {
          this.filtered = false;
          return;
        }

        this.filtered = Object.values(Object.fromEntries((new FormData(this.$el.querySelector('form'))).entries())).filter((i) => !!i).length > 0;
      },
      initFiltered: function () {
        this.filtered = Object.values(Object.fromEntries((new URLSearchParams(window.location.search)).entries())).filter((i) => !!i).length > 0;
      },
      open: false,
      filtered: false,
    }"
  >
    <div @click.outside="open = false">
      <button
        class="inline-flex items-center rounded-md font-semibold text-xs text-center px-4 py-2 bg-transparent border focus:outline-none"
        :class="{ 'bg-primary border-primary text-white hover:bg-primary hover:text-white active:border-primary': filtered, 'bg-transparent border-dark text-dark hover:bg-dark hover:text-white active:border-dark': !filtered }"
        @click="open = !open"
      >
        <x-ui.icon name="filter" />
      </button>

      <div class="relative">
        <div @filtered="isFiltered" x-show="open" style="display: none" class="mt-1 bg-white shadow-xl border border-norma-gray-200 rounded-lg max-h-[70vh] z-10 absolute top-0 left-0 max-w-screen-75 overflow-y-auto custom-scroll">
          {{ $slot }}
        </div>
      </div>
    </div>
  </div>
</div>
