<script setup>
import { onMounted } from 'vue';

const props = defineProps({
  type: { type: String, default: 'basic' },
  maxHeight: { type: Number, default: null },
  label: { type: String, default: null },
  required: { type: Boolean, default: false },
  errors: { type: Array, default: () => [] },
});

const value = defineModel();

onMounted(() => {
  if (window.initTiny) {
    window.initTiny(props.maxHeight ? { maxHeight: props.maxHeight } : {});
  }
});

</script>

<template>
  <label v-if="label" class="text-sm font-medium text-norma-gray-700 block mt-4">
    <span v-if="required" class="text-red-400 mr-1">*</span>
    {{ label }}
  </label>

  <textarea
    v-bind="$attrs"
    v-model="value"
    :required="required"
    rows="8"
    :class="`norma-editor-${type}`"
    class="px-3 py-2 border leading-normal rounded-md shadow-sm border-gray-300 focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50 block mt-1 w-full"
  />

  <div v-if="errors.length > 0" :key="error" class="text-sm text-red-400">
    {{ errors.join(' ') }}
  </div>
</template>
