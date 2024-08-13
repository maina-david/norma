<script setup>
import { ref } from 'vue';
import AppButton from '@/vue/components/AppButton.vue';
import InputElement from '@/vue/components/InputElement.vue';

const props = defineProps({
  updateBodyPayload: { type: Function, required: true },
  triggerExport: { type: Function, required: true },
  cancelExport: { type: Function, required: true },
});

const selected = ref('all');
function generateExport() {
  props.updateBodyPayload({ included_tasks: selected.value });
  props.triggerExport();
}
</script>

<template>
  <div class="bg-white rounded-lg px-6 py-8">
    <div class="font-semibold text-lg">
      {{ $t('actions.export_options.title') }}
    </div>

    <form @submit.prevent="generateExport">
      <InputElement
        v-model="selected"
        name="tasks"
        type="radio"
        value="all"
        :label="$t('actions.export_options.all')"
        required
      />

      <InputElement
        v-model="selected"
        name="tasks"
        type="radio"
        value="by_users"
        :label="$t('actions.export_options.by_users')"
        required
      />

      <InputElement
        v-model="selected"
        name="tasks"
        type="radio"
        value="none"
        :label="$t('actions.export_options.none')"
        required
      />

      <div class="flex justify-end space-x-4 mt-4">
        <AppButton type="button" @click="cancelExport">
          {{ $t('actions.cancel') }}
        </AppButton>

        <AppButton type="submit" theme="primary">
          {{ $t('actions.export') }}
        </AppButton>
      </div>
    </form>
  </div>
</template>
