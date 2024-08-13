<script setup>
import { ref } from 'vue';
import LegalDomainSearch from '@/vue/pages/annotations/components/LegalDomainSearch.vue';
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
    <LegalDomainSearch
      ref="selector"
      with-remove
      multiple
      :location="location"
      @change-object="(e) => emit('change', e)"
    />

    <ReferenceMetaSuggestions no-magi :reference="reference" :selector="() => selector" meta="legalDomains" />
  </div>
</template>
