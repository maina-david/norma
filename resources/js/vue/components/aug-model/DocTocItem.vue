<script setup>
import { computed, inject, ref } from 'vue';
import useCRUD from '@/vue/pages/annotations/composables/useCRUD';
import LibryoIcon from '@/collaborate/components/LibryoIcon.vue';
import { useAxios } from '@/vue/composables/useAxios';

const props = defineProps({
  expression: { type: Object, required: true },
  item: { type: Object, required: true },
});

const axios = useAxios();
const can = inject('can');
const toggleTocSelection = inject('toggleTocSelection');
const tocSelection = inject('tocSelection');
const openReferenceId = inject('openReferenceId');
const changeContent = inject('changeContent');
const fetchAllReferences = inject('fetchAllReferences');
const forIdentification = inject('forIdentification') ?? false;

const children = ref(null);
const isOpen = ref(false);
const hasLoaded = ref(false);
const { items, fetchAll, loading } = useCRUD(`/work-expressions/${props.expression.id}/toc-items/${props.item.id}`, {}, 10000, {}, 100);

const reqScoreLabel = computed(() => {
  if (props.item.requirement_score < 0.5) {
    return '';
  }

  return `${props.item.requirement_score * 100}% chance of having a requirement.`;
});

function handleClick(event, tocItem) {
  if (!tocItem.content_resource_id || tocItem.content_resource_id === '') {
    event.preventDefault();
    return;
  }

  if (tocItem.content_resource_id) {
    changeContent(`/content-resources/${tocItem.content_resource_id}/${tocItem.doc_id}`);
  }

  window.setTimeout(() => window.scrollToItemWhenAvailable(`#${tocItem.uri_fragment}`, '.libryo-legislation', 'smooth'), 200);
}

function toggleChildren() {
  if (!hasLoaded.value) {
    fetchAll(() => {
      hasLoaded.value = true;
      isOpen.value = !isOpen.value;
    });

    return;
  }

  isOpen.value = !isOpen.value;
}

function handleCheckboxChange(event) {
  const isChecked = event.target.checked;
  toggleTocSelection(props.item.id, isChecked);

  if (isOpen.value && children.value) {
    Array.from(children.value.querySelectorAll('input')).forEach((item) => {
      item.checked = isChecked;
      item.dispatchEvent(new Event('change'));
    });
  }
}

function handleInsert() {
  const activeRef = openReferenceId.value ?? '';
  axios.post(`/work-expressions/${props.expression.id}/toc-items/${props.item.id}/insert-from-toc/${activeRef}`)
    .then(() => {
      return fetchAllReferences();
    })
    .then(() => window.toast.success({ message: 'Successfully inserted.' }));
}
function handleUpdate() {
  const activeRef = openReferenceId.value;

  if (activeRef) {
    axios.post(`/work-expressions/${props.expression.id}/toc-items/${props.item.id}/update-from-toc/${activeRef}`)
      .then(() => {
        return fetchAllReferences();
      })
      .then(() => window.toast.success({ message: 'Successfully updated.' }));
  }
}

function getRequirementScoreColour() {
  if (props.item.requirement_score < 0.5) {
    return '';
  }

  if (props.item.requirement_score < 0.7) {
    return 'text-yellow-500';
  }

  return 'text-negative-darker';
}

</script>

<template>
  <div class="flex-grow flex flex-col overflow-hidden pl-2">
    <div v-loading="loading" class="py-2">
      <div class="flex text-libryo-gray-800 relative group">
        <div class="flex-shrink-0 w-6">
          <button v-if="item.children_count > 0" class="w-6 hover:text-primary " @click.prevent="toggleChildren">
            <LibryoIcon :name="isOpen ? 'minus-square' : 'plus-square'" />
          </button>
        </div>

        <div v-if="forIdentification" class="flex-shrink-0 pl-1">
          <input
            type="checkbox"
            class="mr-2 h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded toc-checkbox"
            :value="item.id"
            :checked="tocSelection.includes(item.id)"
            @change="handleCheckboxChange"
          >
        </div>

        <div
          v-tooltip="reqScoreLabel"
          class="flex-grow hover:text-primary cursor-pointer px-2"
          :class="getRequirementScoreColour()"
          @click="(event) => handleClick(event, item)"
        >
          {{ item.label }}
        </div>

        <div v-if="can('collaborate.corpus.reference.create')" class="absolute bg-white right-0 top-0 h-full px-4 hidden items-center space-x-4 group-hover:flex">
          <button
            v-if="!item.has_reference"
            v-tooltip="`Create new citation from this and insert at current position`"
            class="w-6 hover:text-primary"
            @click.prevent.stop="handleInsert"
          >
            <LibryoIcon icon-size="xl" name="rectangle-history-circle-plus" />
          </button>

          <button
            v-if="openReferenceId"
            v-tooltip="`Update the active citation with this`"
            class="w-6 hover:text-primary"
            @click.prevent.stop="handleUpdate"
          >
            <LibryoIcon icon-size="xl" name="recycle" />
          </button>
        </div>
      </div>

      <div v-show="isOpen" ref="children" class="mt-2 divide-y divide-gray-50 border-t border-gray-50">
        <DocTocItem
          v-for="child in items"
          :key="child.id"
          :item="child"
          :expression="expression"
        >
          {{ item.title }}
        </DocTocItem>
      </div>
    </div>
  </div>
</template>
