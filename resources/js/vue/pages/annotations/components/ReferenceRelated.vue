<script setup>
import { groupBy } from 'lodash';
import { computed, ref } from 'vue';
import ReferenceRelatedGroup from '@/vue/pages/annotations/components/ReferenceRelatedGroup.vue';
import RelatedReferencesSelector from '@/vue/pages/annotations/components/RelatedReferencesSelector.vue';

const props = defineProps({
  reference: { type: Object, required: true },
});

const visibility = ref({
  parent_1: true,
  parent_3: true,
  parent_6: true,
  child_1: true,
  child_3: true,
  child_6: true,
});

const children = computed(() => groupBy(props.reference.linked_children, 'link_type'));
const parents = computed(() => groupBy(props.reference.linked_parents, 'link_type'));

</script>

<template>
  <div class="space-y-4">
    <div>
      <RelatedReferencesSelector :reference="reference" />
    </div>

    <ReferenceRelatedGroup
      v-for="(group, key) in parents"
      :key="`parent_${key}`"
      :link-type="key"
      :related="group"
      :reference="reference"
      :visible="visibility[`parent_${key}`]"
      @toggle="visibility[`parent_${key}`] = !visibility[`parent_${key}`]"
    />

    <ReferenceRelatedGroup
      v-for="(group, key) in children"
      :key="`child_${key}`"
      :link-type="key"
      :related="group"
      :reference="reference"
      :visible="visibility[`child_${key}`]"
      child
      @toggle="visibility[`child_${key}`] = !visibility[`child_${key}`]"
    />
  </div>
</template>
