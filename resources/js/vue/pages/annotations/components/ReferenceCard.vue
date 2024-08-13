<script setup>
import { computed, inject, onMounted, ref } from 'vue';
import ReferenceHeader from '@/vue/pages/annotations/components/ReferenceHeader.vue';
import { useState } from '@/vue/composables/useState';
import ReferenceBody from '@/vue/pages/annotations/components/ReferenceBody.vue';

const activateReference = inject('activateReference');
const props = defineProps({
  reference: { type: Object, required: true },
  selected: { type: Boolean, default: false },
  bulkEnabled: { type: Boolean, default: false },
});
const emit = defineEmits(['scroll']);
const [open, setOpen] = useState(false);
const card = ref(null);
const cardColour = computed(() => {
  if (props.reference.status === 1) {
    return 'border-negative';
  }

  if (props.reference.content_draft) {
    return 'border-warning';
  }

  return 'border-gray-200';
});

function handleOpen() {
  setOpen(!open.value);
  if (open.value) {
    emit('scroll', props.reference);
  }
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
      <ReferenceBody v-if="open" :reference="reference" />
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
