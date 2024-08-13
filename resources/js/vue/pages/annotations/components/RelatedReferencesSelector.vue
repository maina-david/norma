<script setup>
import { computed, inject, ref } from 'vue';
import { useState } from '@/vue/composables/useState';
import { useAxios } from '@/vue/composables/useAxios';
import LibryoIcon from '@/collaborate/components/LibryoIcon.vue';
import WorkSelector from '@/vue/pages/annotations/components/WorkSelector.vue';
import AppButton from '@/vue/components/AppButton.vue';
import ReferenceSelector from '@/vue/components/content-management/ReferenceSelector.vue';

const axios = useAxios();
const can = inject('can');
const work = inject('work');
const refresh = inject('refresh');
const props = defineProps({ reference: { type: Object, required: true } });
const selector = ref(null);
const [open, setOpen] = useState(false);
const [selectorVisible, setSelectorVisible] = useState(false);
const linkType = ref(null);
const selectedWork = ref({ ...work });

const types = computed(() => {
  const items = [];
  if (can('collaborate.corpus.reference.link.link_type_1')) {
    items.push({ relation: 'children', type: '1', label: 'Read-with' });
  }
  if (can('collaborate.corpus.reference.link.link_type_3')) {
    items.push({ relation: 'parents', type: '3', label: 'Requirements leading to these consequences' });
    items.push({ relation: 'children', type: '3', label: 'Leads to consequences' });
  }
  if (can('collaborate.corpus.reference.link.link_type_6')) {
    items.push({ relation: 'parents', type: '6', label: 'Amended by' });
    items.push({ relation: 'children', type: '6', label: 'Amends' });
  }

  return items;
});

const canLink = computed(() => types.value.length > 0);

function selectType(type) {
  setOpen(false);
  linkType.value = type;
  setSelectorVisible(true);
}

function onClose() {
  setOpen(false);
  linkType.value = null;
  setSelectorVisible(false);
}

function onWorkSelected(item) {
  selectedWork.value = { ...item };
  selector.value?.fetchAll();
}
function onLink(references) {
  setOpen(false);
  setSelectorVisible(false);

  axios.post(`/references/${props.reference.id}/type/${linkType.value.type}/${linkType.value.relation}`, { references })
    .then(() => refresh());
}
</script>

<template>
  <div>
    <div>
      <AppButton v-if="canLink" @click="() => setOpen(true)">
        Add Relationship
      </AppButton>

      <div v-if="open" class="fixed inset-0 z-[1]" @click="onClose" />

      <div v-if="open" class="relative max-w-xs">
        <div class="shadow-lg rounded-b-lg border border-gray-100 z-10 bg-white py-2 text-sm absolute">
          <button v-for="type in types" :key="`${type.linkType}${type.relation}`" class="block w-full text-left px-4 py-1" @click.prevent="() => selectType(type)">
            <span>{{ type.label }}</span>
          </button>
        </div>
      </div>

      <div v-if="selectorVisible" class="fixed inset-0 w-screen h-screen flex items-center justify-center overflow-hidden z-20 bg-gray-400 bg-opacity-25">
        <div class="py-4 px-4 bg-white rounded-lg border border-gray-200 shadow-lg overflow-hidden flex flex-col w-full max-w-[85vw] h-[75vh]">
          <div class="flex-shrink-0 mb-4 flex items-center justify-between">
            <div class="font-semibold px-2">
              <div v-if="linkType">
                {{ linkType.label || '' }}
              </div>
              <div v-if="selectedWork" class="text-xs text-libryo-gray-600">
                Linking to {{ selectedWork.title }}
              </div>
            </div>

            <button class="p-2 hover:text-primary" @click="onClose">
              <LibryoIcon name="times" />
            </button>
          </div>

          <div class="flex-grow overflow-hidden">
            <div class="grid grid-cols-2 gap-4 h-full w-full overflow-hidden">
              <div class="w-full h-full overflow-hidden pt-1">
                <KeepAlive>
                  <WorkSelector :initial-location="work.primary_location_id" @select="onWorkSelected" />
                </KeepAlive>
              </div>

              <div v-if="selectedWork" class="w-full h-full overflow-hidden pt-1">
                <KeepAlive>
                  <ReferenceSelector
                    ref="selector"
                    :has-requirement="reference.requirement_count + reference.requirement_draft_count > 0"
                    :work="selectedWork"
                    @select="onLink"
                    @close="onClose"
                  />
                </KeepAlive>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
