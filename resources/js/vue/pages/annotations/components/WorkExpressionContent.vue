<script setup>
import { ref } from 'vue';
import useCRUD from '@/vue/pages/annotations/composables/useCRUD';
import Pagination from '@/vue/components/Pagination.vue';
import WorkExpressionDocContent from '@/vue/components/aug-model/WorkExpressionDocContent.vue';
import useHighlighter from '@/vue/pages/annotations/composables/useHighlighter';
import { useState } from '@/vue/composables/useState';

const [scrollTo, setScrollTo] = useState(null);
const contentElement = ref(null);
const props = defineProps({ expression: { type: Object, required: true } });
const { scrollToSelection } = useHighlighter(contentElement);
const { items, fetchAll, loading, pagination, changePage } = useCRUD(`/work-expressions/${props.expression.id}/content`, {}, 1);

function postFetch(timeout = 1) {
  if (scrollTo.value) {
    setTimeout(() => {
      scrollToSelection(scrollTo.value);
      setScrollTo(null);
    }, timeout);
  }
}

function changePageAndScroll(page, callback = null) {
  return changePage(page, callback).then(() => contentElement.value.parentElement.scrollTo(0, 0));
}
function handleChangePage(page) {
  if (pagination.page !== page) {
    changePageAndScroll(page, () => postFetch(500));
    return;
  }

  postFetch();
}

fetchAll();

defineExpose({ scrollToSelection, changePage: handleChangePage, setScrollTo });

</script>

<template>
  <WorkExpressionDocContent v-if="items[0] && items[0].doc" :doc="items[0].doc" :expression="expression" />

  <div v-else-if="items[0] && items[0].content !== null" v-loading="loading" class="flex-grow flex flex-col overflow-hidden">
    <div class="flex-grow relative overflow-y-auto custom-scroll bg-white p-6 norma-legislation">
      <div v-if="items[0] && items[0].content" ref="contentElement" v-html="items[0].content" />
    </div>

    <Pagination :last-page="pagination.lastPage" :per-page="pagination.perPage" :current="pagination.page" @page="changePageAndScroll" />
  </div>
</template>
