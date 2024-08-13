<script setup>
import AppIcon from '@/vue/components/AppIcon.vue';

defineProps({
  icon: { type: String, required: true },
  title: { type: String, required: true },
  size: { type: String, default: '20' },
});
</script>

<template>
  <div class="my-8">
    <div class="flex justify-center">
      <AppIcon class="text-primary opacity-80" type="duotone" :name="icon" :size="size" />
    </div>

    <div class="my-4">
      <div class="font-bold text-md text-center">
        {{ title }}
      </div>

      <div class="font-light text-libryo-gray-500 text-sm text-center mt-2">
        {{ $t('interface.when_added_they_show_up') }}
      </div>
    </div>
  </div>
</template>
