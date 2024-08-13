<script setup>
import { ref } from 'vue';
import CategorySearch from '@/vue/pages/annotations/components/CategorySearch.vue';
import ReferenceMetaSuggestions from '@/vue/pages/annotations/components/meta/ReferenceMetaSuggestions.vue';

defineProps({
  location: { type: Number, default: null },
  reference: { type: Object, required: true },
});

const selector = ref(null);
const emit = defineEmits(['change']);

</script>

<template>
  <div class="space-y-2">
    <CategorySearch
      ref="selector"
      with-remove
      multiple
      :location="location"
      @change-object="(e) => emit('change', e)"
    />

    <ReferenceMetaSuggestions :reference="reference" :selector="() => selector" meta="categories" />
  </div>
</template>
