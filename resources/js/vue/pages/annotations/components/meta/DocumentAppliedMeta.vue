<script setup>
import { computed, inject } from 'vue';

const applied = inject('appliedMeta');
const emit = defineEmits(['select']);
const props = defineProps({ meta: { type: String, required: true } });

const inDocument = computed(() => {
  const aliases = {
    contextQuestions: 'questions',
    categories: 'topics',
  };

  const value = [...(applied.value[aliases[props.meta] || props.meta] || [])];

  return value.sort((a, b) => {
    if (a.title > b.title) return 1;
    if (a.title < b.title) return -1;
    return 0;
  });
});

</script>

<template>
  <div class="text-xs space-y-1 flex flex-col items-start">
    <div v-for="item in inDocument" :key="item.id" class="rounded-full bg-secondary text-white font-semibold px-2 py-0.5 cursor-pointer hover:bg-secondary-darker" @click="() => emit('select', item)">
      {{ item.title }}
    </div>
  </div>
</template>
