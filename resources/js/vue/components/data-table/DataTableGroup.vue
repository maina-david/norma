<script setup>
import { ref } from 'vue';
import AppIcon from '@/vue/components/AppIcon.vue';

const loaded = ref(false);
const open = ref(false);

function toggle() {
  loaded.value = true;
  open.value = !open.value;
}
</script>

<template>
  <div class="rounded-lg px-1" :class="{'pb-8': open }">
    <div class="bg-white rounded-lg shadow cursor-pointer px-4 py-2 flex items-center" :class="{ 'rounded-b-lg': !open }" @click="toggle">
      <span class="w-4">
        <AppIcon :name="open ? 'angle-down' : 'angle-right'" size="3" />
      </span>
      <slot name="label" />
    </div>

    <div v-if="loaded" v-show="open" class="pt-4 px-2">
      <slot />
    </div>
  </div>
</template>
