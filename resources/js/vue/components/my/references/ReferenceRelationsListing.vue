<script setup>
import { ref } from 'vue';
import AppIcon from '@/vue/components/AppIcon.vue';
import { useAxios } from '@/vue/composables/useAxios';
import { usePagination } from '@/vue/pages/annotations/composables/usePagination';
import ReferenceRelationListingItem from '@/vue/components/my/references/ReferenceRelationListingItem.vue';
import AppPagination from '@/vue/components/AppPagination.vue';

const props = defineProps({
  referenceId: { type: Number, required: true },
  count: { type: Number, required: true },
  relation: { type: String, required: true },
});

const langMap = { 'amendments': 6, 'read-withs': 1, 'consequences': 3 };
const open = ref(false);
const loaded = ref(false);
const loading = ref(false);
const linked = ref([]);
const axios = useAxios();
const { pagination, changePage, getPaginationQueryParams, updatePagination } = usePagination(20);

function fetchRelations() {
  loading.value = true;
  axios.get(`/references/${props.referenceId}/read-withs/${props.relation}`, { params: getPaginationQueryParams() })
    .then(({ data }) => data)
    .then(({ meta, data }) => {
      linked.value = [...data];
      updatePagination(meta);
    })
    .finally(() => {
      loaded.value = true;
      loading.value = false;
    });
}

function toggleOpen() {
  open.value = !open.value;

  if (!loaded.value) {
    fetchRelations();
  }
}

function handleChangePage(page) {
  changePage(page, fetchRelations);
}
</script>

<template>
  <div v-loading="loading">
    <div class="flex items-center cursor-pointer px-4 py-5 sm:px-6" @click.stop="toggleOpen">
      <div class="flex-grow leading-6 font-medium">
        {{ $t(`corpus.reference.link_type.${langMap[relation]}`) }} ({{ count }})
      </div>

      <div class="flex-shrink-0">
        <AppIcon v-if="open" name="chevron-up" />
        <AppIcon v-else name="chevron-down" />
      </div>
    </div>

    <div v-if="open" class="bg-gray-50">
      <div class=" divide-y divide-gray-200">
        <ReferenceRelationListingItem v-for="item in linked" :key="item.id" :related="item" />
      </div>

      <div v-if="pagination.lastPage > 1" class="py-2 bg-white">
        <AppPagination :last-page="pagination.lastPage" :per-page="pagination.perPage" :current="pagination.page" @page="handleChangePage" />
      </div>
    </div>
  </div>
</template>
