<script setup>
import { inject, ref } from 'vue';
import { useTaskModel } from '@/vue/composables/my/actions/useTaskModel';
import InputElement from '@/vue/components/InputElement.vue';
import AppButton from '@/vue/components/AppButton.vue';

const emit = defineEmits(['change']);

const props = defineProps({
  row: { type: Object, required: true },
  rowIndex: { type: Number, default: null },
  field: { type: String, required: true },
  type: { type: String, default: 'text' },
  formatValue: { type: Function, default: (a) => a },
});

const updateDataTableRow = props.rowIndex !== null ? inject('updateDataTableRow') : () => {};
const { updateField, loading } = useTaskModel();

const currentValue = ref(props.row[props.field]);
const inputValue = ref(props.row[props.field]);
const editing = ref(false);

function changeValue(selected) {
  updateField(props.row.id, props.field, selected)
    .then((changed) => {
      emit('change', changed[props.field]);
      currentValue.value = changed[props.field];

      if (props.rowIndex !== null) {
        updateDataTableRow(props.rowIndex, changed);
      }
    });
}

function cancelEdit() {
  inputValue.value = currentValue.value;
  editing.value = false;
}
</script>

<template>
  <div>
    <div v-if="loading" class="whitespace-nowrap rounded-lg px-3 flex items-center">
      . . .
    </div>

    <div v-else>
      <div v-if="!editing" class="cursor-pointer" @click.stop="editing = !editing">
        {{ currentValue ? formatValue(currentValue) : '-' }}
      </div>

      <form v-if="editing" action="#" @submit.prevent="() => changeValue(inputValue)">
        <InputElement v-model="inputValue" :type="type" />

        <div class="flex justify-end mt-2 space-x-4">
          <AppButton theme="negative" type="button" @click="cancelEdit">
            {{ $t('actions.cancel') }}
          </AppButton>
          <AppButton theme="primary" type="submit">
            {{ $t('actions.save') }}
          </AppButton>
        </div>
      </form>
    </div>
  </div>
</template>
