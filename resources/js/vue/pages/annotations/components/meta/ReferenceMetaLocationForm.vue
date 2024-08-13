<script setup>
import { ref } from 'vue';
import LocationSearch from '@/vue/pages/annotations/components/LocationSearch.vue';
import ReferenceMetaSuggestions from '@/vue/pages/annotations/components/meta/ReferenceMetaSuggestions.vue';

defineProps({
  noMagi: { type: Boolean, default: false },
  reference: { type: Object, required: true },
});

const selector = ref(null);
const emit = defineEmits(['change']);
</script>

<template>
  <div class="space-y-2">
    <LocationSearch ref="selector" with-remove multiple @change-object="(e) => emit('change', e)" />

    <ReferenceMetaSuggestions :no-magi="noMagi" :reference="reference" :selector="() => selector" meta="locations" />
  </div>
</template>
