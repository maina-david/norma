<script setup>
import { computed, ref } from 'vue';
import NormaIcon from '@/collaborate/components/NormaIcon.vue';
import { useState } from '@/vue/composables/useState';
import VuePagination from '@/vue/components/Pagination.vue';
import useCRUD from '@/vue/pages/annotations/composables/useCRUD';
import SourceSelector from '@/vue/pages/annotations/components/SourceSelector.vue';
import LocationSearch from '@/vue/pages/annotations/components/LocationSearch.vue';

const props = defineProps({ initialLocation: { type: [Number, null], default: null } });
const emit = defineEmits(['select']);

const [filterOpen, setFilterOpen] = useState(false);
const searchTerm = ref('');
const source = ref(null);
const location = ref(props.initialLocation);

const filters = computed(() => {
  const body = { sort: 'title' };

  if (source.value) {
    body.filters = [`source_id,eq,${source.value}`];
  }

  if (location.value) {
    body.jurisdiction = location.value;
  }

  if (searchTerm.value && searchTerm.value.length > 3) {
    body.search = searchTerm.value;
  }

  return body;
});

const { items, loading, fetchAll, pagination, changePage } = useCRUD('/catalogue-docs', filters, 50);

function handleSelect(work) {
  emit('select', work);
}
function onSourceChange(value) {
  source.value = value;
  setFilterOpen(false);
}

function onLocationChange(value) {
  location.value = value;
  setFilterOpen(false);
}

function handleSearch(event) {
  searchTerm.value = event.target.value;
}

fetchAll();
</script>

<template>
  <div class="bg-white px-2 flex flex-col h-full">
    <div class="flex-shrink-0 flex">
      <form class="flex items-center flex-grow relative" @submit.prevent="fetchAll">
        <input type="search" class="flex-grow focus:ring focus:ring-primary focus:outline-none border-gray-300 rounded-l-lg pl-10 py-2 border" @input="handleSearch">

        <button type="submit" class="absolute left-3 top-0 h-full text-norma-gray-600">
          <NormaIcon name="magnifying-glass" />
        </button>
      </form>

      <button type="button" class="bg-norma-gray-200 flex-shrink-0 rounded-r-lg px-4 border-gray-300" @click="() => setFilterOpen(!filterOpen)">
        <NormaIcon name="filter" />
      </button>
    </div>

    <div v-if="filterOpen" class="fixed inset-0 z-[1]" @click="() => setFilterOpen(false)" />

    <div class="flex-shrink-0 relative">
      <div v-if="filterOpen" class="absolute top-0 right-0 bg-white z-10 p-4 bg-white shadow-lg rounded-b-lg border border-gray-100 max-w-xs w-screen">
        <div class="mb-1 font-semibold text-sm flex items-center justify-between">
          <span>Source</span>

          <button v-if="source" class="text-primary" type="button" @click="source = null">
            Clear
          </button>
        </div>
        <SourceSelector ref="selector" with-remove @change="onSourceChange" />

        <div class="mb-1 mt-2 font-semibold text-sm flex items-center justify-between">
          <span>Jurisdiction</span>

          <button v-if="location" class="text-primary" type="button" @click="location = null">
            Clear
          </button>
        </div>
        <LocationSearch ref="selector" with-remove @change="onLocationChange" />
      </div>
    </div>

    <div class="py-2 flex-grow flex flex-col overflow-hidden relative">
      <div v-if="loading" class="loader-container inset-0 absolute w-full h-full flex items-center justify-center bg-norma-gray-50">
        <div class="loading">
          <div class="effect-1 effects" />
          <div class="effect-2 effects" />
          <div class="effect-3 effects" />
        </div>
      </div>
      <div class="flex-grow overflow-y-auto custom-scroll">
        <table class="font-semibold text-sm w-full">
          <thead>
            <tr class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-norma-gray-500 border-y border-gray-300">
              <th class="py-2 pr-8">
                TITLE
              </th>
              <th class="py-2 pr-8">
                TYPE
              </th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="items.length === 0">
              <td class="font-normal" colspan="2">
                The current filters don't match anything.
              </td>
            </tr>
            <tr v-for="(item, index) in items" :key="item.id" :class="{ 'bg-norma-gray-50': index%2 === 0 }">
              <td class="py-2 cursor-pointer text-primary pr-4" @click="() => handleSelect(item)">
                <div>{{ item.title }}</div>
                <div v-if="item.title_translation" class="text-sm text-norma-gray-500">
                  {{ item.title_translation }}
                </div>
                <div class="mt-1 text-norma-gray-500 italic">
                  {{ item.latest_doc_created_at ?? '-' }}
                </div>
              </td>
              <td class="py-2">
                <a v-if="item.view_url" :href="item.view_url" target="_blank" class="text-primary">
                  Preview
                </a>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="flex-shrink-0">
        <VuePagination :last-page="pagination.lastPage" :per-page="pagination.perPage" :current="pagination.page" @page="changePage" />
      </div>
    </div>
  </div>
</template>
