<script setup>
import { ref } from 'vue';
import AppIcon from '@/vue/components/AppIcon.vue';

const visible = ref(false);
const loaded = ref(true);

function toggle() {
  visible.value = !visible.value;
  loaded.value = true;
}
</script>

<template>
  <div>
    <div>
      <slot name="trigger" :toggle="toggle" />
    </div>

    <transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div v-if="loaded" v-show="visible" class="z-40 fixed inset-0 h-screen w-screen flex flex-col items-center justify-center overflow-y-auto bg-gray-500 bg-opacity-30 pt-10">
        <div class="h-full">
          <div class="shadow-xl rounded-lg relative">
            <div class="w-full absolute top-0 right-0 flex justify-end pr-4 pt-4">
              <button class="bg-white rounded-full flex items-center justify-center" @click="visible = false">
                <AppIcon name="times" size="6" />
              </button>
            </div>

            <slot :toggle="toggle" :visible="visible" />
          </div>

          <div class="h-10 w-full" />
        </div>
      </div>
    </transition>
  </div>
</template>
