<script setup>
import { onMounted, onUnmounted, ref } from 'vue';
import { createPopper } from '@popperjs/core/lib/popper-lite.js';
import preventOverflow from '@popperjs/core/lib/modifiers/preventOverflow.js';
import flip from '@popperjs/core/lib/modifiers/flip.js';

const props = defineProps({
  anchor: { type: String, default: 'bottom-start' },
});

const visible = ref(false);
const loaded = ref(true);
const triggerEl = ref(null);
const bodyEl = ref(null);
const popper = ref(null);

function show() {
  visible.value = true;
  loaded.value = true;

  popper.value.setOptions((options) => ({
    ...options,
    modifiers: [
      ...options.modifiers,
      { name: 'eventListeners', enabled: true },
    ],
  }));
  popper.value.update();
}

function hide() {
  visible.value = false;

  popper.value.setOptions((options) => ({
    ...options,
    modifiers: [
      ...options.modifiers,
      { name: 'eventListeners', enabled: false },
    ],
  }));
  popper.value.update();
}

function toggle() {
  visible.value ? hide() : show();
}

onMounted(() => {
  popper.value = createPopper(triggerEl.value, bodyEl.value, {
    modifiers: [preventOverflow, flip],
    strategy: 'fixed',
    placement: props.anchor,
  });
});

onUnmounted(() => {
  popper.value?.destroy();
});
</script>

<template>
  <div>
    <div ref="triggerEl">
      <slot name="trigger" :toggle="toggle" />
    </div>

    <div v-if="visible" class="z-10 fixed inset-0" @click="hide" />

    <div ref="bodyEl" class="z-20">
      <transition
        enter-active-class="transition ease-out duration-100"
        enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100"
        leave-active-class="transition ease-in duration-75"
        leave-from-class="transform opacity-100 scale-100"
        leave-to-class="transform opacity-0 scale-95"
      >
        <div v-if="loaded" v-show="visible" class="border border-gray-100 shadow-xl rounded-lg overflow-hidden">
          <slot :toggle="toggle" />
        </div>
      </transition>
    </div>
  </div>
</template>
