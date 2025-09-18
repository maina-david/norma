<script setup>
import TomSelect from 'tom-select/dist/js/tom-select.popular';
import { onMounted, onUnmounted, ref } from 'vue';

defineOptions({ inheritAttrs: false });
const props = defineProps({
  options: { type: Array, default: () => [] },
  allowEmpty: { type: Boolean, default: false },
  multiple: { type: Boolean, default: false },
  placeholder: { type: String, default: null },
  label: { type: String, default: null },
  required: { type: Boolean, default: false },
  errors: { type: Array, default: () => [] },
});
const value = defineModel();
const tom = ref(null);
const element = ref(null);

onMounted(() => {
  tom.value = new TomSelect(element.value, {
    plugins: props.multiple ? ['remove_button'] : [],
    allowEmptyOption: props.allowEmpty,
    placeholder: props.placeholder,
    maxOptions: null,
    render: {
      option: function (data) {
        let detailStr = data.$option.getAttribute('data-detail') || '';
        detailStr = detailStr.length > 0 ? '<div class="text-norma-gray-500">' + detailStr + '</div>' : '';

        return '<div>' +
            '<div>' + data.$option.innerHTML + '</div>' +
            detailStr +
            '</div>';
      },
      item: function (data, escape) {
        let detailStr = data.$option.getAttribute('data-detail') || '';

        return '<div title="' + escape(detailStr || '') + '">' + data.$option.innerHTML + '</div>';
      },
    },
  });

  tom.value.on('change', (detail) => {
    value.value = detail;
  });
});

onUnmounted(() => {
  tom.value?.destroy();
});
</script>

<template>
  <label v-if="label" class="text-sm font-medium text-norma-gray-700 block mt-4">
    <span v-if="required" class="text-red-400 mr-1">*</span>
    {{ label }}
  </label>

  <select
    v-bind="$attrs"
    ref="element"
    v-model="value"
    :multiple="multiple"
    :required="required"
    class="mr-2 text-primary focus:ring-primary border-gray-300 rounded-lg w-full"
  >
    <option v-for="item in options" :key="item.value" :value="item.value">
      {{ item.label }}
    </option>
  </select>

  <div v-if="errors.length > 0" :key="error" class="text-sm text-red-400">
    {{ errors.join(' ') }}
  </div>
</template>
