<script setup>
import { inject } from 'vue';
import { metaInfo } from '@/vue/composables/useMetaVisibility';
import AppTabs from '@/vue/components/AppTabs.vue';
import DocumentAppliedMeta from '@/vue/pages/annotations/components/meta/DocumentAppliedMeta.vue';
import MagiGeneratedMeta from '@/vue/pages/annotations/components/meta/MagiGeneratedMeta.vue';

const magi = inject('magi');

const props = defineProps({
  meta: { type: String, required: true },
  noMagi: { type: Boolean, default: false },
  selector: { type: Function, required: true },
  reference: { type: Object, required: true },
});

const tabs = [
  `${metaInfo[props.meta].label} applied to the document`,
];

if (magi && !props.noMagi && props.reference.id !== 'bulk') {
  tabs.push('Get Suggestions');
}
function handleSelect(selected) {
  props.selector().tomselect().addOption([selected]);
  props.selector().tomselect().addItem(selected.id);
  props.selector().tomselect().refreshOptions(false);
  props.selector().tomselect().refreshItems();
}
</script>

<template>
  <div>
    <div class="mt-6">
      <AppTabs :tabs="tabs" :active="tabs[0]">
        <template #default="{ active }">
          <DocumentAppliedMeta v-if="active === tabs[0]" :meta="meta" @select="handleSelect" />
          <keep-alive>
            <MagiGeneratedMeta v-if="active === tabs[1]" :meta="meta" :reference="reference" @select="handleSelect" />
          </keep-alive>
        </template>
      </AppTabs>
    </div>
  </div>
</template>
