<script setup>
import CountryFlag from '@/vue/components/CountryFlag.vue';

defineProps({
  related: { type: Object, required: true },
});
</script>

<template>
  <a class="flex items-center px-4 py-2" :href="`/requirements/citations/${related.id}`" target="_blank">
    <div class="flex-grow">
      <div class="font-semibold text-primary">
        {{ related.title }}
      </div>
      <div class="text-norma-gray-500">
        {{ related.work_title }}
      </div>
    </div>

    <div class="flex-shrink-0">
      <CountryFlag :country-code="related.flag" class="h-8 w-8 rounded-full" />
    </div>
  </a>
</template>
