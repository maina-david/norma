<script setup>
import { computed, ref } from 'vue';
import { useAxios } from '@/vue/composables/useAxios';
import ReferenceRelationsListing from '@/vue/components/my/references/ReferenceRelationsListing.vue';

const props =defineProps({
  referenceId: { type: Number, required: true },
  workId: { type: Number, required: true },
});

const axios = useAxios();
const loading = ref(false);
const amendments = ref(0);
const readWith = ref(0);
const consequences = ref(0);

const totalReads = computed(() => amendments.value + readWith.value + consequences.value);

function fetchReadWiths() {
  loading.value = true;
  axios.get(`/references/${props.referenceId}/read-withs`)
    .then(({ data }) => data)
    .then(({ data }) => {
      amendments.value = data.amendments;
      readWith.value = data.read_with;
      consequences.value = data.consequences;
    })
    .finally(() => {
      loading.value = false;
    });
}

fetchReadWiths();
</script>

<template>
  <div class="-mt-4">
    <div v-if="!loading && (totalReads === 0)" class="text-center text-norma-gray-600 pt-8">
      {{ $t('requirements.no_read_with') }}
    </div>

    <div class="divide-y divide-norma-gray-100">
      <ReferenceRelationsListing
        v-if="amendments"
        relation="amendments"
        :count="amendments"
        :reference-id="referenceId"
      />

      <ReferenceRelationsListing
        v-if="readWith"
        relation="read-withs"
        :count="readWith"
        :reference-id="referenceId"
      />

      <ReferenceRelationsListing
        v-if="consequences"
        relation="consequences"
        :count="consequences"
        :reference-id="referenceId"
      />
    </div>
  </div>
</template>
