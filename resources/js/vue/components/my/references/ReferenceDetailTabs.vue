<script setup>
import { useI18n } from 'vue-i18n';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppTabs from '@/vue/components/AppTabs.vue';
import ReferenceContent from '@/vue/components/my/references/ReferenceContent.vue';
import ReferenceReadWith from '@/vue/components/my/references/ReferenceReadWith.vue';
import ReferenceSummary from '@/vue/components/my/references/ReferenceSummary.vue';
import ReferenceActionAreas from '@/vue/components/my/actions/ReferenceLinkedActionArea.vue';
import ApplicabilityListing from '@/vue/components/my/compilations/ApplicabilityListing.vue';

defineProps({
  referenceId: { type: [String, Number], required: true },
  workId: { type: [String, Number], required: true },
});

const { t } = useI18n({ useScope: 'global' });
const page = usePage();

const tabs = computed(()=> {
  const baseTabs = [
    {
      id: 'detail',
      label: t('requirements.requirement_details'),
      icon: 'file-alt',
    },
    {
      id: 'read-with',
      label: t('requirements.read_withs'),
      icon: 'object-union',
    },
    {
      id: 'notes',
      label: t('requirements.summary.notes'),
      icon: 'sticky-note',
    },
  ];

  if (page.props.stream.modules.actions && page.props.stream.org_modules.actions) {
    baseTabs.push({
      id: 'linked-action-areas',
      label: t('requirements.linked_action_areas'),
      icon: 'clipboard-list',
    });
  }

  if (page.props.stream.single) {
    baseTabs.push({
      id: 'applicability',
      label: t('requirements.applicability'),
      icon: 'ballot-check',
    });
  }

  return baseTabs;
});
</script>

<template>
  <AppTabs :tabs="tabs" :active="tabs[0].id">
    <template #default="{ active }">
      <KeepAlive>
        <ReferenceContent v-if="active === 'detail'" :reference-id="referenceId" :work-id="workId" />
      </KeepAlive>

      <KeepAlive>
        <ReferenceReadWith v-if="active === 'read-with'" :reference-id="referenceId" :work-id="workId" />
      </KeepAlive>

      <KeepAlive>
        <ReferenceSummary v-if="active === 'notes'" :reference-id="referenceId" :work-id="workId" />
      </KeepAlive>

      <KeepAlive>
        <ReferenceActionAreas v-if="active === 'linked-action-areas'" :reference-id="referenceId" />
      </KeepAlive>

      <KeepAlive>
        <ApplicabilityListing v-if="active === 'applicability'" :reference-id="referenceId" />
      </KeepAlive>
    </template>
  </AppTabs>
</template>
