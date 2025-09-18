<script setup>
import {inject, provide, ref} from 'vue';
import useCRUD from '@/vue/pages/annotations/composables/useCRUD';
import DocTocItem from '@/vue/components/aug-model/DocTocItem.vue';
import {useAxios} from '@/vue/composables/useAxios';
import NormaIcon from '@/collaborate/components/NormaIcon.vue';
import ConfirmButton from '@/vue/components/ConfirmButton.vue';

const props = defineProps({ expression: { type: Object, required: true } });
const { items, fetchAll, loading } = useCRUD(`/work-expressions/${props.expression.id}/toc-items`, {}, 10000);

const axios = useAxios();
const can = inject('can');
const forIdentification = inject('forIdentification') ?? false;
const fetchAllReferences = inject('fetchAllReferences');
const openReferenceId = inject('openReferenceId');
const selectedItems = ref([]);
const loadingAction = ref(false);

function toggleTocSelection(id, value) {
  if (value && !selectedItems.value.includes(id)) {
    selectedItems.value.push(id);
    return;
  }

  const index = selectedItems.value.indexOf(id);

  if (!value && index !== -1) {
    selectedItems.value.splice(index, 1);
  }
}

function handleInsert() {
  const items = Array.from(document.querySelectorAll('.toc-listing input:checked')).map((el) => el.value);

  const activeRef = openReferenceId.value ?? '';

  loadingAction.value = true;
  axios.post(`/work-expressions/${props.expression.id}/toc-items/bulk/insert-from-toc/${activeRef}`, { toc_items:items })
    .then(() => fetchAllReferences())
    .then(() => window.toast.success({ message: 'Successfully inserted.' }))
    .finally(() => {
      loadingAction.value = false;
    });
}

function  handleCheckboxChange(event) {
  document.querySelectorAll('.toc-checkbox').forEach((item) => {
    item.checked = event.target.checked;
    item.dispatchEvent(new Event('change'));
  });
}

fetchAll();

provide('toggleTocSelection', toggleTocSelection);
provide('tocSelection', selectedItems);
</script>

<template>
  <div class="flex-grow flex flex-col overflow-hidden h-full toc-listing">
    <div v-loading="loading || loadingAction" class="text-left text-xs flex-grow relative overflow-y-auto custom-scroll bg-white pr-1 py-2 norma-legislation divide-y divide-gray-50">
      <div
        v-if="forIdentification"
        class="ml-1 py-2 space-x-4 flex items-center justify-between px-1.5 border border-gray-200 rounded-lg bg-neutral-100 mt-1 sticky top-0 z-[9]"
      >
        <div class="flex-shrink-0 pl-6">
          <input
            type="checkbox"
            class="mr-2 h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
            @change="handleCheckboxChange"
          >
        </div>

        <ConfirmButton
          v-if="can('collaborate.corpus.reference.create') && selectedItems.length > 0"
          method="post"
          title="Create Citations"
          message="Are you sure you want to create the selected citations and insert at the given position?"
          base-url="/"
          theme="primary"
          @confirm="handleInsert"
        >
          <span class="flex items-center">
            <NormaIcon name="rectangle-history-circle-plus" />
            <span class="ml-2 whitespace-nowrap">Create Citations</span>
          </span>
        </ConfirmButton>
      </div>

      <DocTocItem
        v-for="item in items"
        :key="item.id"
        :item="item"
        :expression="expression"
      />
    </div>
  </div>
</template>
