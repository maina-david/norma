<template>
  <div class="pointer-events-none fixed z-20 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      <transition
        enter-active-class="ease-out duration-300"
        enter-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="ease-in duration-200"
        leave-class="opacity-100"
        leave-to-class="opacity-0"
      >
        <div v-if="isOpen" class="pointer-events-auto bg-opacity-25 z-0 fixed left-0 top-0 w-screen h-screen bg-gray-400" @click="isOpen = false" />
      </transition>

      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

      <transition
        enter-active-class="ease-out duration-300"
        enter-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        enter-to-class="opacity-100 translate-y-0 sm:scale-100"
        leave-active-class="ease-in duration-200"
        leave-class="opacity-100 translate-y-0 sm:scale-100"
        leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
      >
        <div v-if="isOpen" class="z-20 pointer-events-auto inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:p-6">
          <div>
            <h3 id="modal-title" class="text-xl leading-6 font-medium text-norma-gray-900 flex items-center">
              {{ title }}
            </h3>
            <div class="mt-2">
              <slot>
                <p class="text-sm text-norma-gray-500">
                  {{ message }}
                </p>
              </slot>
            </div>
          </div>
          <div class="mt-5 sm:mt-6 flex justify-between">
            <button type="button" class="mt-3 inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-norma-gray-700 hover:bg-norma-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 sm:mt-0 sm:col-start-1 sm:text-sm" @click.prevent="rejectAction">
              {{ rejectText }}
            </button>
            <button type="button" class="inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:col-start-2 sm:text-sm" @click.prevent="confirmAction">
              {{ confirmText }}
            </button>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>

<script>
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'ConfirmAction',
  data() {
    return {
      isOpen: false,
      title: '',
      message: '',
      confirmText: '',
      rejectText: '',
      resolve: () => {},
      reject: () => {},
    };
  },
  created() {
    this.$onEvent('confirm', (payload) => {
      this.title = payload.title;
      this.message = payload.message;
      this.confirmText = payload.confirmText || 'Confirm';
      this.rejectText = payload.rejectText || 'Cancel';
      this.resolve = payload.resolve;
      this.reject = payload.reject;
      this.isOpen = true;
    });
  },
  methods: {
    reset() {
      this.isOpen = false;
    },
    confirmAction() {
      this.resolve();
      this.reset();
    },
    rejectAction() {
      this.reject();
      this.reset();
    },
  },
});
</script>
