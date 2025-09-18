<script setup>
defineProps({
  label: { type: String, default: null },
  required: { type: Boolean, default: false },
  errors: { type: Array, default: () => [] },
  maxlength: { type: String, default: null },
  rows: { type: String, default: '6' },
});
defineOptions({ inheritAttrs: false });
const model = defineModel();
</script>

<template>
  <div>
    <label v-if="label" class="text-sm font-medium text-norma-gray-700 mt-4 block">
      <span v-if="required" class="text-red-400 mr-1">*</span>
      <span>{{ label }}</span>
    </label>

    <textarea
      v-model="model"
      class="mr-2 border-norma-gray-300 focus:border-primary focus:ring-primary block leading-normal rounded-md shadow-sm"
      v-bind="$attrs"
      :required="required"
      :maxlength="maxlength"
      :rows="rows"
    />

    <div v-if="errors.length > 0" class="text-sm text-red-400">
      {{ errors.join(' ') }}
    </div>
  </div>
</template>
