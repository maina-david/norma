<script setup>
import { ref } from 'vue';
import AppButton from '@/vue/components/AppButton.vue';
import LibryoIcon from '@/collaborate/components/LibryoIcon.vue';
import DropDown from '@/vue/components/DropDown.vue';
import SelectElement from '@/vue/components/SelectElement.vue';
import InputElement from '@/vue/components/InputElement.vue';

defineProps({
  actions : { type: Array, required: true },
});
const emit = defineEmits(['apply']);
const formVisible = ref(false);
const selectedAction = ref(null);

function handleTrigger(action, toggle) {
  toggle();
  if (!action.component && !action.type) {
    emit('apply', { action: action.name, payload: { [action.name]: null } });
    return;
  }

  selectedAction.value = action;
  formVisible.value = true;
}

function handleCancel() {
  selectedAction.value = null;
  formVisible.value = false;
}
function handleForm() {
  emit('apply', { action: selectedAction.value.name, payload: { [selectedAction.value.name]: selectedAction.value.value } });
  handleCancel();
}
</script>

<template>
  <div>
    <DropDown>
      <template #trigger="{ toggle }">
        <AppButton @click="toggle">
          <libryo-icon name="play" />
        </AppButton>
      </template>

      <template #default="{ toggle }">
        <div class="bg-white py-2 whitespace-nowrap space-y-1">
          <div v-for="(action, index) in actions" :key="index">
            <button class="px-4 hover:text-primary hover:font-semibold" @click="() => handleTrigger(action, toggle)">
              {{ $t(action.label) }}
            </button>
          </div>
        </div>
      </template>
    </DropDown>

    <div v-if="selectedAction && formVisible" class="fixed inset-0 z-20 bg-gray-300 bg-opacity-50">
      <transition
        enter-active-class="transition ease-out duration-100"
        enter-from-class="transform opacity-0 scale-95"
        enter-to-class="transform opacity-100 scale-100"
        leave-active-class="transition ease-in duration-75"
        leave-from-class="transform opacity-100 scale-100"
        leave-to-class="transform opacity-0 scale-95"
      >
        <div v-show="formVisible" class="h-full w-full flex items-center justify-center">
          <div class="py-4 px-6 bg-white shadow-lg rounded-md w-96 max-w-screen-75">
            <form action="#" @submit.prevent="handleForm">
              <label class="text-sm font-medium text-libryo-gray-700 block mt-4 mb-2">{{ $t(selectedAction.label) }}</label>

              <component
                :is="selectedAction.component()"
                v-if="selectedAction.component"
                v-model="selectedAction.value"
                required
                :multiple="selectedAction.multiple"
              />

              <SelectElement
                v-else-if="selectedAction.type === 'select'"
                v-model="selectedAction.value"
                required
                :multiple="selectedAction.multiple"
                :options="selectedAction.options"
              />

              <InputElement v-else v-model="selectedAction.value" required :type="selectedAction.type" />

              <div class="flex justify-end space-x-4 mt-4">
                <AppButton @click.prevent="handleCancel">
                  {{ $t('actions.cancel') }}
                </AppButton>

                <AppButton theme="primary" type="submit">
                  {{ $t('actions.apply') }}
                </AppButton>
              </div>
            </form>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>
