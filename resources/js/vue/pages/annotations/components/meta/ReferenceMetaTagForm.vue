<script setup>
import { ref } from 'vue';
import TagSearch from '@/vue/pages/annotations/components/TagSearch.vue';
import ReferenceMetaSuggestions from '@/vue/pages/annotations/components/meta/ReferenceMetaSuggestions.vue';

defineProps({
  reference: { type: Object, required: true },
  noMagi: { type: Boolean, default: false },
});

const selector = ref(null);
const emit = defineEmits(['change']);
</script>

<template>
  <div class="space-y-2">
    <TagSearch ref="selector" with-remove multiple @change-object="(e) => emit('change', e)" />

    <ReferenceMetaSuggestions :no-magi="noMagi" :reference="reference" :selector="() => selector" meta="tags" />
  </div>
</template>
