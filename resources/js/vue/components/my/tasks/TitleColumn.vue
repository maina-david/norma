<script setup>
defineProps({
  row: { type: Object, required: true },
});
</script>

<template>
  <div>
    <div class="text-primary font-semibold">
      {{ row.title }}
    </div>

    <div v-if="row.sub_title" class="text-xs text-libryo-gray-500 mt-1">
      {{ row.sub_title }}
    </div>

    <div v-if="row.libryo_title" class="text-xs text-libryo-gray-500 mt-1">
      {{ row.libryo_title }}
    </div>
  </div>
</template>
