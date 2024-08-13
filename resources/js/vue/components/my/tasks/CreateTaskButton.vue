<script setup>
import CreateTaskForm from '@/vue/components/my/tasks/CreateTaskForm.vue';
import AppButton from '@/vue/components/AppButton.vue';
import AppModal from '@/vue/components/AppModal.vue';
import AppIcon from '@/vue/components/AppIcon.vue';

defineProps({
  value: { type: Object, default: null },
  iconOnly: { type: Boolean, default: false },
  reference: { type: Boolean, default: false },
  type: { type: String, required: true },
  typeId: { type: [String, Number], required: true },
  actionAreas: { type: Array, default: () => [] },
});

const emit = defineEmits(['create']);

function handleCreate(toggle) {
  toggle();
  emit('create');
}
</script>

<template>
  <AppModal anchor="right">
    <template #trigger="{ toggle }">
      <AppButton theme="primary" @click="toggle">
        <span class="flex items-center">
          <AppIcon name="plus" />
          <span v-if="!iconOnly" class="ml-2">{{ $t('tasks.create_task') }}</span>
        </span>
      </AppButton>
    </template>

    <template #default="{ toggle }">
      <div class="max-w-screen-75 bg-white rounded-lg pt-2 pb-8 px-8">
        <h3 class="mt-4 font-semibold text-xl">
          {{ $t('tasks.create_task') }}
        </h3>

        <CreateTaskForm
          :value="value"
          :reference="reference"
          :type="type"
          :type-id="typeId"
          :action-areas="actionAreas"
          @create="() => handleCreate(toggle)"
        />
      </div>
    </template>
  </AppModal>
</template>
