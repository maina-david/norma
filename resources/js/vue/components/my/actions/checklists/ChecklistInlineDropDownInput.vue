<script setup>
import { inject, ref } from 'vue';
import DropDown from '@/vue/components/DropDown.vue';
import AppIcon from '@/vue/components/AppIcon.vue';
import { useTaskModel } from '@/vue/composables/my/actions/useTaskModel';
import AppButton from '@/vue/components/AppButton.vue';
import WysiwygEditor from '@/vue/components/WysiwygEditor.vue';
import InputElement from '@/vue/components/InputElement.vue';
import TextareaElement from '@/vue/components/TextareaElement.vue';

// getStatusLabel
const emit = defineEmits(['change', 'full-change']);

const props = defineProps({
  row: { type: Object, required: true },
  rowIndex: { type: Number, default: null },
  field: { type: String, required: true },
  getBackgroundColour: { type: Function, default: null },
  getLabel: { type: Function, default: null },
  type: { type: String, default: 'text' },
  maxlength: { type: String, default: null },
  rows: { type: String, default: '6' },
});

const updateDataTableRow = props.rowIndex !== null ? inject('updateDataTableRow') : () => {};

const { updateField, loading } = useTaskModel();

const currentValue = ref(props.row[props.field]);
const inputValue = ref(props.row[props.field]);
const errors = ref({});

function changeValue(selected, toggle) {
  loading.value = true;
  errors.value = {};
  updateField(props.row.id, props.field, selected)
    .then((changed) => {
      emit('change', changed[props.field]);
      emit('full-change', { ...changed });
      currentValue.value = changed[props.field];

      if (props.rowIndex !== null) {
        updateDataTableRow(props.rowIndex, changed);
      }

      if (typeof toggle === 'function') {
        toggle();
      }
    })
    .catch(({ response }) => {
      errors.value = { ...response.data.errors };
    })
    .finally(() => {
      loading.value = false;
    });
}

function changeInputValue(value) {
  inputValue.value = value;
}
</script>

<template>
  <div class="flex justify-center">
    <DropDown @click.stop="">
      <template #trigger="{ toggle }">
        <div v-if="loading" class="whitespace-nowrap rounded-lg px-3 flex items-center" :class="getBackgroundColour ? getBackgroundColour(currentValue) : {}">
          . . .
        </div>
        <template v-else>
          <slot name="display" :toggle="toggle" :change-value="changeValue">
            <button class="rounded-lg px-3 flex items-center" :class="getBackgroundColour ? getBackgroundColour(currentValue) : {}" @click="toggle">
              <span class="whitespace-nowrap mr-3 text-sm font-medium">{{ getLabel ? getLabel(currentValue) : currentValue }}</span>
              <AppIcon name="chevron-down" size="3" />
            </button>
          </slot>
        </template>
      </template>

      <template #default="{ toggle }">
        <div v-loading="loading">
          <div class="text-left bg-white py-4 flex flex-col max-h-[40vh] overflow-y-auto custom-scroll px-4">
            <slot :toggle="toggle" :change-value="changeValue">
              <form action="#" @submit.prevent="() => { changeValue(inputValue, toggle);}">
                <slot name="inputs" :input-value="inputValue" :update="changeInputValue">
                  <WysiwygEditor v-if="type === 'wysiwyg'" v-model="inputValue" :errors="errors[field]" :max-height="200" />
                  <TextareaElement
                    v-else-if="type === 'textarea'"
                    v-model="inputValue"
                    class="w-full"
                    :maxlength="maxlength"
                    :rows="rows"
                    :errors="errors[field]"
                  />
                  <InputElement v-else v-model="inputValue" :errors="errors[field]" :type="type" />
                </slot>

                <div class="flex justify-end mt-4 space-x-4">
                  <AppButton type="button" theme="negative">
                    Clear
                  </AppButton>
                  <AppButton type="submit">
                    Save
                  </AppButton>
                </div>
              </form>
            </slot>
          </div>
        </div>
      </template>
    </DropDown>
  </div>
</template>
