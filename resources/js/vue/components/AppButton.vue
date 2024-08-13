<script setup>
defineProps({
  theme: { type: String, default: 'default' },
  noBorder: { type: Boolean, default: false },
});

const styles = {
  danger: 'border-negative text-negative hover:text-white hover:bg-negative',
  negative: 'border-negative text-negative hover:text-white hover:bg-negative',
  default: 'border-libryo-gray-500 text-libryo-gray-500 hover:text-white hover:bg-libryo-gray-500',
  primary: 'border-primary text-primary hover:text-white hover:bg-primary',
};
</script>

<template>
  <button class="px-4 py-2 rounded-md text-xs font-semibold" :class="`${styles[theme]} ${noBorder ? '' : 'border'}`">
    <slot />
  </button>
</template>
