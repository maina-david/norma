<script setup>
import { inject, ref } from 'vue';
import AppButton from '@/vue/components/AppButton.vue';
import NormaIcon from '@/collaborate/components/NormaIcon.vue';
import DeleteButton from '@/vue/components/DeleteButton.vue';

const props = defineProps({
  reference: { type: Object, required: true },
});

const setOpenReferenceId = inject('setOpenReferenceId');
const expression = inject('expression');
const fetchAllReferences = inject('fetchAllReferences');
const can = inject('can');
const loading = ref(false);

function performAction(name) {
  loading.value = true;
  const route = `/corpus/expressions/${expression.id}/identify/references/${props.reference.id}/${name}`;
  axios.post(route, {}, { baseURL: '/' })
    .then(() => fetchAllReferences())
    .finally(() => {
      loading.value = false;
    });
}

function handleConfirmDelete(){
  setOpenReferenceId(null);
  fetchAllReferences();
}

</script>

<template>
  <div v-loading="loading" class="group-hover:flex hidden">
    <AppButton
      v-if="reference.position > 1"
      v-tooltip="'Move Up'"
      no-border
      @click="() => performAction('move-up')"
    >
      <NormaIcon name="arrow-up" />
    </AppButton>

    <AppButton
      v-tooltip="'Move Down'"
      no-border
      @click="() => performAction('move-down')"
    >
      <NormaIcon name="arrow-down" />
    </AppButton>

    <AppButton
      v-if="can('collaborate.corpus.reference.create')"
      v-tooltip="'Insert Below'"
      no-border
      @click="() => performAction('insert')"
    >
      <NormaIcon name="plus-minus" />
    </AppButton>

    <DeleteButton
      v-if="reference.content_draft && can('collaborate.corpus.reference.delete')"
      v-tooltip="'Delete'"
      base-url="/"
      :target="`/corpus/expressions/identify/references/${reference.id}`"
      no-border
      @confirm="handleConfirmDelete"
    >
      <NormaIcon name="trash-alt" />
    </DeleteButton>

    <AppButton
      v-if="!reference.content_draft && can('collaborate.corpus.reference.request-update')"
      v-tooltip="'Request Update'"
      no-border
      @click="() => performAction('request-update')"
    >
      <NormaIcon name="code-compare" />
    </AppButton>
  </div>
</template>
