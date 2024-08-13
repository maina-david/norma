<script setup>
import { inject } from 'vue';
import useCRUD from '@/vue/pages/annotations/composables/useCRUD';
import Pagination from '@/vue/components/Pagination.vue';

const props = defineProps({ expression: { type: Object, required: true } });
const scrollToReference = inject('scrollToReference');
const changeReferencePage = inject('changeReferencePage');

const { items, fetchAll, loading, pagination, changePage } = useCRUD(`/work-expressions/${props.expression.id}/toc`, {}, 100);

function findAndScrollReference(reference, tries = 0) {
  if (tries < 6) {
    const node = document.querySelector(`#ref-${reference.id}`);

    if (!node) {
      setTimeout(() => findAndScrollReference(reference, tries + 1), 500);
      return;
    }

    node.scrollIntoView();
    node.classList.add('selecting');
    setTimeout(() => node.classList.remove('selecting'), 500);
  }
}

function handleClick(reference, index) {
  scrollToReference(reference);
  // The pager in references is 50 while this is 100
  changeReferencePage((pagination.page * 2) - (index < 50 ? 1 : 0));
  setTimeout(() => findAndScrollReference(reference), 500);
}

fetchAll();
</script>

<template>
  <div class="flex-grow flex flex-col overflow-hidden">
    <div v-loading="loading" class="text-left font-semibold text-sm flex-grow relative overflow-y-auto custom-scroll bg-white px-4 py-2 libryo-legislation divide-y divide-gray-200">
      <div
        v-for="(item, index) in items"
        :key="item.id"
        :style="`padding-left:${item.level}rem`"
        class="cursor-pointer hover:text-primary"
        @click="() => handleClick(item, index)"
      >
        {{ item.title }}
      </div>
    </div>

    <Pagination :last-page="pagination.lastPage" :per-page="pagination.perPage" :current="pagination.page" @page="changePage" />
  </div>
</template>
