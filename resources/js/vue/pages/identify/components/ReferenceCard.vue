<script setup>
import { computed, inject, onMounted, ref } from 'vue';
import ReferenceHeader from './ReferenceHeader.vue';
import ReferenceBody from './ReferenceBody.vue';

const setOpenReferenceId = inject('setOpenReferenceId');
const activateReference = inject('activateReference');
const openReferenceId = inject('openReferenceId');
const props = defineProps({
  reference: { type: Object, required: true },
  selected: { type: Boolean, default: false },
  bulkEnabled: { type: Boolean, default: false },
});
const emit = defineEmits(['scroll']);
const card = ref(null);
const cardColour = computed(() => {
  if (props.reference.content_draft) {
    return 'border-negative';
  }

  return 'border-gray-200';
});

function handleOpen() {
  emit('scroll', props.reference);
  setOpenReferenceId(props.reference.id);
}

onMounted(() => {
  if (activateReference.value === props.reference.id) {
    card.value.scrollIntoView();
    setTimeout(() => {
      handleOpen();
    }, 1000);
  }
});

</script>

<template>
  <div ref="card" class="ref-card rounded border" :class="cardColour">
    <ReferenceHeader :bulk-enabled="bulkEnabled" :selected="selected" :reference="reference" @open="handleOpen" />

    <Transition name="fade">
      <ReferenceBody v-if="openReferenceId === reference.id" :reference="reference" />
    </Transition>
  </div>
</template>

<style scoped>
.ref-card:not(.selecting) {
  @apply bg-white;
}
.ref-card.selecting  {
  @apply bg-primary;
}

.fade-enter-active {
  transition: all 0.2s ease-out;
}

.fade-leave-active {
  transition: all 0.1s cubic-bezier(1, 0.5, 0.8, 1);
}

.fade-enter-from,
.fade-leave-to {
  transform: translateY(-20px);
  opacity: 0;
}
</style>
