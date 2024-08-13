<script setup>
import { Link } from '@inertiajs/vue3';
import AppIcon from '@/vue/components/AppIcon.vue';

defineProps({
  target: { type: String, required: true },
});
</script>

<template>
  <Link
    :href="target"
    class="inline-flex items-center rounded-full font-semibold text-xs text-center transition ease-in-out duration-150 tracking-widest p-2 bg-transparent border border-primary text-primary hover:bg-primary hover:text-white active:border-primary focus:outline-none focus:border-primary focus:ring-2 ring-primary"
  >
    <AppIcon name="chevron-left" size="4" />
  </Link>
</template>
