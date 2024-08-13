<script setup>
import { inject } from 'vue';
import ReferenceRelatedItem from '@/vue/pages/annotations/components/ReferenceRelatedItem.vue';
import LibryoIcon from '@/collaborate/components/LibryoIcon.vue';
import { confirm } from '@/vue/plugins/bus';
import { useAxios } from '@/vue/composables/useAxios';

const can = inject('can');
const refresh = inject('refresh');
const axios = useAxios();

const props = defineProps({
  visible: { type: Boolean, required: true },
  reference: { type: Object, required: true },
  related: { type: Array, required: true },
  linkType: { type: [Number, String], required: true },
  child: { type: Boolean, default: false },
});

const emit = defineEmits(['toggle']);

const labels = props.child
  ? {
    1: 'Read-with',
    3: 'Leads to consequences',
    6: 'Amends',
  }
  : {
    1: 'Read-with',
    3: 'Requirements leading to these consequences',
    6: 'Amended by',
  };

const canDelete = can('collaborate.corpus.reference.link.delete') && can(`collaborate.corpus.reference.link.link_type_${props.linkType}`);
const deleteEndpoint = `/references/${props.reference.id}/type/${props.linkType}/${props.child ? 'children' : 'parents'}`;

function confirmDelete(type) {
  if (canDelete) {
    confirm({ title: 'Unlink', message: `Are you sure you want to unlink all ${labels[type].toLowerCase()} statements?` })
      .then(() => axios.delete(deleteEndpoint))
      .then(() => {
        window.toast.success({ message: 'Unlinked successfully.' });
        refresh();
      })
      .catch(() => {});
  }
}

</script>

<template>
  <div>
    <div class="flex items-center justify-between cursor-pointer" @click="() => emit('toggle')">
      <div>
        <span class="w-3">
          <LibryoIcon :name="visible ? 'angle-down' : 'angle-right'" icon-size="md" />
        </span>
        <span class="ml-2 font-semibold">{{ labels[linkType] }}</span>
      </div>

      <div v-if="canDelete">
        <button class="border border-gray-300 rounded-md px-2 py-1 hover:border-negative hover:text-negative" @click.stop="() => confirmDelete(linkType)">
          <LibryoIcon name="unlink" icon-size="md" />
        </button>
      </div>
    </div>

    <div v-if="visible" class="border-t border-gray-200 mt-1 space-y-2 pt-2 pl-5">
      <ReferenceRelatedItem
        v-for="item in related"
        :key="item.id"
        :reference="reference"
        :related="item"
        :child="child"
        :link-type="linkType"
        :can-delete="canDelete"
        :delete-endpoint="deleteEndpoint"
      />
    </div>
  </div>
</template>
