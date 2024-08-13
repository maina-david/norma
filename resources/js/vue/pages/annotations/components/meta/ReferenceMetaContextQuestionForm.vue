<script setup>
import { ref } from 'vue';
import { useState } from '@/vue/composables/useState';
import CategorySearch from '@/vue/pages/annotations/components/CategorySearch.vue';
import ContextQuestionSearch from '@/vue/pages/annotations/components/ContextQuestionSearch.vue';
import ReferenceMetaSuggestions from '@/vue/pages/annotations/components/meta/ReferenceMetaSuggestions.vue';

defineProps({
  location: { type: Number, default: null },
  reference: { type: Object, required: true },
});

const selector = ref(null);
const emit = defineEmits(['change']);
const [categories, setCategories] = useState([]);

function changeCategories(items) {
  setCategories([...items]);
}
</script>

<template>
  <div class="space-y-2">
    <CategorySearch
      is-context
      with-remove
      :location="location"
      multiple
      @change="changeCategories"
    />

    <ContextQuestionSearch
      ref="selector"
      with-remove
      multiple
      :categories="categories"
      :location="location"
      @change-object="(e) => emit('change', e)"
    />

    <ReferenceMetaSuggestions :reference="reference" :selector="() => selector" meta="contextQuestions" />
  </div>
</template>
