<script setup>
import CountryFlag from '@/vue/components/CountryFlag.vue';

defineProps({
  row: { type: Object, required: true },
});
</script>

<template>
  <div class="flex">
    <div class="flex-shrink-0 pr-4">
      <CountryFlag class="h-8 w-8 rounded-full" :country-code="row.flag" />
    </div>

    <div class="flex-grow">
      <div class="text-primary font-semibold">
        {{ row.title }}
      </div>

      <div class="text-xs text-norma-gray-500 mt-1">
        {{ row.work_title ?? '-' }}
      </div>
    </div>
  </div>
</template>
