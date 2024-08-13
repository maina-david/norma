<script setup>
defineProps({
  label: { type: String, default: null },
  type: { type: String, default: 'text' },
  required: { type: Boolean, default: false },
  placeholder: { type: String, default: null },
  errors: { type: Array, default: () => [] },
});
defineOptions({ inheritAttrs: false });
const model = defineModel();
</script>

<template>
  <div>
    <label v-if="label && ['checkbox', 'radio'].includes(type)" class="text-sm font-medium text-libryo-gray-700 mt-4 flex">
      <input
        v-model="model"
        class="mr-2 mt-1 h-4 w-4 border-libryo-gray-300 text-primary focus:border-primary focus:ring-primary block leading-normal rounded-md shadow-sm"
        :class="{ 'w-full': !['checkbox', 'radio'].includes(type) }"
        :type="type"
        v-bind="$attrs"
        :required="required"
        :placeholder="placeholder"
      >
      <span v-if="required && type !== 'radio'" class="text-red-400 mr-1">*</span>
      <span>{{ label }}</span>
    </label>

    <template v-else>
      <label v-if="label" class="text-sm font-medium text-libryo-gray-700 mt-4 block">
        <span v-if="required" class="text-red-400 mr-1">*</span>
        <span>{{ label }}</span>
      </label>

      <input
        v-model="model"
        class="mr-2 border-libryo-gray-300 focus:border-primary focus:ring-primary block leading-normal rounded-md shadow-sm"
        :class="type === 'checkbox' ? '' : 'w-full'"
        :type="type"
        v-bind="$attrs"
        :required="required"
        :placeholder="placeholder"
      >
    </template>

    <div v-if="errors.length > 0" class="text-sm text-red-400">
      {{ errors.join(' ') }}
    </div>
  </div>
</template>
