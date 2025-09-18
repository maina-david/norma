<script setup>
import { computed, ref } from 'vue';
import { debounce } from 'lodash';
import NormaIcon from '@/collaborate/components/NormaIcon.vue';
import useCRUD from '@/vue/pages/annotations/composables/useCRUD';
import { useReferenceVisibility } from '@/vue/pages/annotations/composables/useReferenceVisibility';
import { useBulkActions } from '@/vue/pages/annotations/composables/useBulkActions';
import PaginationComponent from '@/vue/components/Pagination.vue';
import InputElement from '@/vue/components/InputElement.vue';
import AppButton from '@/vue/components/AppButton.vue';
import ReferenceVisibility from './ReferenceVisibility.vue';

const props = defineProps({
  work: { type: Object, required: true },
  hasRequirement: { type: Boolean, default: false },
});

const emit = defineEmits(['select', 'close']);
const searchTerm = ref('');

const { selected, toggleSelected: toggleMainSelected, setSelected, setSelectedObject } = useBulkActions();
const { referenceVisibility, visibilityFilter, toggleReferenceVisibility, setDetailedFilters } = useReferenceVisibility(props.hasRequirement ? {} : { requirement : 3 });

const selectedIds = computed(() => Object.keys(selected).filter((i) => selected[i]));
const filters = computed(() => {
  const body = { ...visibilityFilter.value };

  if (searchTerm.value && searchTerm.value.length > 3) {
    body.search = searchTerm.value;
  }

  return body;
});

const { items, getIds, fetchAll, pagination, changePage, loading } = useCRUD(() => `/works/${props.work.id}/references`, filters);

function toggleFilters(item) {
  if (props.hasRequirement || item !== 'requirement') {
    toggleReferenceVisibility(item);
  }
}
function toggleSelected(key) {
  const item = items.value.filter((i) => i.id === key);
  toggleMainSelected(key, item[0] || null);
}

function toggleSelectAll(event) {
  setSelected(getIds(), event.target.checked);
  setSelectedObject(items.value);
}
function handleSelect() {
  if (selectedIds.value.length > 0) {
    emit('select', selectedIds.value);
    return;
  }
  window.toast.error({ message: 'Please select items to link.' });
}

const handleSearch = debounce((event) => {
  searchTerm.value = event.target.value;
}, 500);

defineExpose({ fetchAll });

fetchAll();
</script>

<template>
  <div class="bg-white flex flex-col h-full">
    <!--    <div class="flex-shrink-0 flex px-2">-->
    <!--      <form class="flex items-center flex-grow relative" @submit.prevent="fetchAll">-->
    <!--        <input type="search" class="flex-grow focus:ring focus:ring-primary focus:outline-none border-gray-300 rounded-lg pl-10 py-2 border" @input="handleSearch">-->

    <!--        <button type="submit" class="absolute left-3 top-0 h-full text-norma-gray-600">-->
    <!--          <NormaIcon name="magnifying-glass" />-->
    <!--        </button>-->
    <!--      </form>-->
    <!--    </div>-->

    <div class="flex-shrink-0">
      <ReferenceVisibility
        no-toggles
        v-bind="referenceVisibility"
        @toggle="toggleFilters"
        @bulk="toggleSelectAll"
        @apply="(e) => setDetailedFilters(e)"
      >
        <template #right>
          <div class="px-4 w-full pb-1">
            <form class="flex items-center flex-grow relative" @submit.prevent="fetchAll">
              <input type="search" class="flex-grow focus:ring focus:ring-primary focus:outline-none border-gray-300 rounded-lg pl-10 py-2 border" @input="handleSearch">

              <button type="submit" class="absolute left-3 top-0 h-full text-norma-gray-600">
                <NormaIcon name="magnifying-glass" />
              </button>
            </form>
          </div>
        </template>
      </ReferenceVisibility>
    </div>

    <div v-loading="loading" class="py-2 flex-grow flex flex-col overflow-hidden">
      <div class="flex-grow overflow-y-auto custom-scroll space-y-2">
        <div v-for="reference in items" :key="reference.id" class="rounded border border-gray-200">
          <div class="flex items-center justify-between pl-4 pr-2">
            <div class="cursor-pointer font-semibold text-sm py-2 flex-grow pr-2 text-primary" @click="() => emit('open')">
              {{ reference.title ?? reference.content_draft?.title ?? '-' }}
            </div>

            <div class="flex-shrink-0 flex items-center space-x-5">
              <span href="#" class="r-taggable" :class="{ 'active': reference.summary_count !== null, 'active-draft': reference.summary_draft_count !== null }">
                <NormaIcon v-if="reference.summary_draft_count !== null" :name="reference.summary_draft_count > 10 ? 'file-alt' : 'file'" icon-size="md" />
                <NormaIcon v-else :name="reference.summary_count > 10 ? 'file-alt' : 'file'" icon-size="md" />
              </span>

              <span href="#" class="r-taggable" :class="{ 'active': reference.requirement_count, 'active-draft': reference.requirement_draft_count }">
                <NormaIcon type="fas" name="marker" icon-size="md" />
              </span>

              <span href="#" class="r-taggable" :class="{ 'active': reference.linked_children_count || reference.linked_parents_count }">
                <NormaIcon name="paperclip" icon-size="md" />
              </span>

              <span href="#" class="r-taggable" :class="{ 'active': reference.collaborate_comments_count }">
                <NormaIcon name="comments" icon-size="md" />
              </span>

              <div class="w-6">
                <InputElement
                  class="w-4 h-4"
                  :checked="selected[reference.id] || false"
                  type="checkbox"
                  @change="() => toggleSelected(reference.id)"
                />
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="flex-shrink-0 pt-2">
        <PaginationComponent :last-page="pagination.lastPage" :per-page="pagination.perPage" :current="pagination.page" @page="changePage" />
      </div>

      <div class="flex-shrink-0 flex justify-end w-full space-x-4 pt-2 pr-2">
        <AppButton @click.prevent="() => emit('close')">
          Cancel
        </AppButton>
        <AppButton :theme="selectedIds.length < 1 ? 'default' : 'primary'" @click.prevent="handleSelect">
          <span>Link Selected ({{ selectedIds.length }})</span>
        </AppButton>
      </div>
    </div>
  </div>
</template>
