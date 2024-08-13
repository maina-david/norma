<script setup>
import { ref } from 'vue';
import { useState } from '@/vue/composables/useState';
import CategorySearch from '@/vue/pages/annotations/components/CategorySearch.vue';
import ReferenceMetaSuggestions from '@/vue/pages/annotations/components/meta/ReferenceMetaSuggestions.vue';
import ActionAreasSearch from '@/vue/pages/annotations/components/ActionAreasSearch.vue';

defineProps({
  location: { type: Number, default: null },
  reference: { type: Object, required: true },
});

const emit = defineEmits(['change']);
const [categories, setCategories] = useState([]);
const selector = ref(null);
function changeCategories(items) {
  setCategories([...items]);
}
</script>

<template>
  <div class="space-y-2">
    <CategorySearch
      with-remove
      :location="location"
      is-actions
      multiple
      @change="changeCategories"
    />

    <ActionAreasSearch
      ref="selector"
      with-remove
      multiple
      :categories="categories"
      :location="location"
      @change-object="(e) => emit('change', e)"
    />

    <ReferenceMetaSuggestions :selector="() => selector" meta="actionAreas" :reference="reference" />
  </div>
</template>
