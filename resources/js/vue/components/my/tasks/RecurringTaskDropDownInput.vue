<script setup>
import { ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { RRule } from 'rrule';
import DropDown from '@/vue/components/DropDown.vue';
import AppIcon from '@/vue/components/AppIcon.vue';
import AppButton from '@/vue/components/AppButton.vue';
import InputElement from '@/vue/components/InputElement.vue';
import SelectElement from '@/vue/components/SelectElement.vue';
import { useAxios } from '@/vue/composables/useAxios';
import { Frequency, MonthSelection } from '@/enums/actions/tasks/tasks-recurrence-frequency';

const props = defineProps({
  task: { type: Object, default: null },
});

const emit = defineEmits(['create']);
const { t } = useI18n({ useScope: 'global' });
const axios = useAxios();
const loading = ref(false);
const errors = ref({});

const form = ref({
  startDate: new Date().toISOString().split('T')[0],
  frequencyNumber: 1,
  frequencyUnit: Frequency.day,
  monthSelection: null,
  selectedDays: [],
  monthDay: null,
  weekDayName: null,
  endCondition: 'never',
  endDate: new Date(new Date().setMonth(new Date().getMonth() + 1)).toISOString().split('T')[0],
  occurrences: null,
});

const frequencyOptions = Frequency.forSelector();

const days = [
  { value: 'MO', label: 'M' },
  { value: 'TU', label: 'T' },
  { value: 'WE', label: 'W' },
  { value: 'TH', label: 'Th' },
  { value: 'FR', label: 'F' },
  { value: 'SA', label: 'Sa' },
  { value: 'SU', label: 'Su' },
];

const monthDays = Array.from({ length: 31 }, (_, i) => ({ value: i + 1, label: `Day ${i + 1}` }));

const weekdays = [
  { value: t('timestamps.days_initials.Monday'), label: t('timestamps.days.Monday') },
  { value: t('timestamps.days_initials.Tuesday'), label: t('timestamps.days.Tuesday') },
  { value: t('timestamps.days_initials.Wednesday'), label: t('timestamps.days.Wednesday') },
  { value: t('timestamps.days_initials.Thursday'), label: t('timestamps.days.Thursday') },
  { value: t('timestamps.days_initials.Friday'), label: t('timestamps.days.Friday') },
  { value: t('timestamps.days_initials.Saturday'), label: t('timestamps.days.Saturday') },
  { value: t('timestamps.days_initials.Sunday'), label: t('timestamps.days.Sunday') },
];

function resetForm() {
  form.value = {
    startDate: new Date().toISOString().split('T')[0],
    frequencyNumber: 1,
    frequencyUnit: Frequency.day,
    monthSelection: null,
    monthDay: null,
    weekDayName: null,
    endCondition: 'never',
    endDate: null,
    occurrences: null,
    selectedDays: [],
    ...(props.value ?? {}),
  };
  loading.value = false;
}

resetForm();

function handleSubmit() {
  loading.value = true;
  return axios.post(`/actions/tasks/${props.task.hash_id}/recurrence`, {
    ...form.value,
  })
    .then(() => {
      resetForm();
      emit('create');
      window.toast.success({ message: t('actions.saved_successfully') });
    })
    .catch(({ response }) => {
      if (response.status === 422) {
        errors.value = response.data.errors;
      } else {
        window.toast.error({ message: t('actions.error_occurred') });
      }
    })
    .finally(() => {
      loading.value = false;
    });
}

function clearRecurrence() {
  loading.value = true;
  return axios.post(`/actions/tasks/${props.task.hash_id}/recurrence/clear`)
    .then(() => {
      resetForm();
      emit('create');
      window.toast.success({ message: t('actions.saved_successfully') });
    })
    .catch(({ response }) => {
      console.error(response);
    })
    .finally(() => {
      loading.value = false;
    });
}

onFrequencyChange();
function onFrequencyChange() {
  if (form.value.frequencyUnit !== Frequency.week) {
    form.value.selectedDays = [];
  }
}

watch(
  [() => form.value.monthSelection],
  ([newMonthSelection]) => {
    if (newMonthSelection === MonthSelection.DAY_OF_MONTH) {
      form.value.weekDay = null;
      form.value.weekDayName = null;
    } else if (newMonthSelection === MonthSelection.WEEK_OF_MONTH) {
      form.value.monthDay = null;
    }
  },
  { immediate: true },
);

function formatRRule(string) {
  if (string.length > 1) {
    return string.charAt(0).toUpperCase() + string.slice(1);
  } else {
    return '';
  }
}

const computedRRule = ref('');
const nextTaskDate = ref('');

watch(
  [() => form.value],
  () => {
    updateRRuleAndNextTaskDate();
  },
  { deep: true },
);

function updateRRuleAndNextTaskDate() {
  loading.value = true;
  const rule = new RRule({
    freq: RRule.WEEKLY,
    interval: form.value.frequencyNumber,
    byweekday: form.value.selectedDays.map((day) => RRule[day]),
    dtstart: new Date(form.value.startDate),
    until: form.value.endCondition === 'onDate' ? new Date(form.value.endDate) : undefined,
    count: form.value.endCondition === 'afterOccurrences' ? form.value.occurrences : undefined,
  });

  computedRRule.value = rule.toString();

  const occurrences = rule.all();
  if (occurrences.length > 0) {
    nextTaskDate.value = occurrences[0];
  } else {
    nextTaskDate.value = '';
  }

  loading.value = false;
}

function formatNextTaskDate(date) {
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  const formattedDate = new Date(date).toLocaleDateString(undefined, options);
  return formattedDate.charAt(0).toUpperCase() + formattedDate.slice(1);
}
</script>

<template>
  <div class="flex justify-center">
    <DropDown @click.stop="">
      <template #trigger="{ toggle }">
        <button class="rounded-lg px-3 flex items-center space-x-2" @click="() => { resetForm(); toggle(); }">
          <AppIcon name="calendar" size="4" />
          <span v-if="!task.recurrence_rule || task.recurrence_rule == ''">{{ $t('tasks.recurring.add_recurrence') }}</span>
          <span v-else>{{ formatRRule(task.recurrence_rule) }}</span>
          <AppIcon name="chevron-down" size="3" />
        </button>
      </template>

      <template #default="{ toggle }">
        <div v-loading="loading" class="p-4 bg-white rounded shadow-lg">
          <form action="#" @submit.prevent="() => { handleSubmit(); toggle(); }">
            <div class="mb-4">
              <span class="text-primary underline">First Task is on {{ formatNextTaskDate(nextTaskDate) }}</span>
            </div>
            <div>
              <label for="form.startDate">
                <span class="font-bold">Recurrence Start</span>
                <InputElement
                  v-model="form.startDate"
                  :errors="errors.startDate ?? []"
                  type="date"
                  required
                />
              </label>
            </div>
            <div class="flex items-center mt-4 space-x-2">
              <label class="w-full">
                <span class="font-bold">{{ $t('tasks.recurring.recurring_tasks_frequency') }}</span>
                <div class="flex space-x-2 mt-4">
                  <label for="form.frequencyNumber">Frequency Interval
                    <InputElement
                      v-model="form.frequencyNumber"
                      :errors="errors.frequencyNumber ?? []"
                      type="number"
                      min="1"
                      required
                    />
                  </label>
                  <label for="form.frequencyUnit">Frequency Unit
                    <SelectElement
                      v-model="form.frequencyUnit"
                      :errors="errors.frequencyUnit ?? []"
                      :options="frequencyOptions"
                      required
                      @change="onFrequencyChange"
                    />
                  </label>
                </div>
              </label>
            </div>

            <div v-if="form.frequencyUnit == Frequency.week" class="flex items-center mt-4 space-x-2">
              <div class="flex space-x-2">
                <span v-for="day in days" :key="day.value">
                  <InputElement
                    v-model="form.selectedDays"
                    type="checkbox"
                    :value="day.value"
                    :label="day.label"
                  />
                </span>
              </div>
            </div>

            <div v-if="form.frequencyUnit == Frequency.month" class="mt-4">
              <div class="flex items-center space-x-2">
                <input
                  id="selectDayOfMonth"
                  v-model="form.monthSelection"
                  type="radio"
                  value="dayOfMonth"
                  class="text-primary"
                >
                <SelectElement
                  v-model="form.monthDay"
                  :errors="errors.monthDay ?? []"
                  :options="monthDays"
                  placeholder="Select Day of the Month"
                  :disabled="form.monthSelection == 'weekOfMonth'"
                />
              </div>
              <div class="flex items-center mt-4 space-x-2">
                <input
                  id="selectWeekOfMonth"
                  v-model="form.monthSelection"
                  type="radio"
                  value="weekOfMonth"
                  class="text-primary"
                >
                <SelectElement
                  v-model="form.weekDayName"
                  :errors="errors.weekDayName ?? []"
                  :options="weekdays"
                  placeholder="Select Weekday"
                  :disabled="form.monthSelection == 'dayOfMonth'"
                />
              </div>
            </div>

            <div class="mt-4">
              <label class="block mb-2 font-semibold text-gray-700" :for="form.endCondition">{{ $t('tasks.recurring.tasks_frequency_end') }}</label>
              <div class="mb-4">
                <InputElement
                  id="never"
                  v-model="form.endCondition"
                  type="radio"
                  value="never"
                  :label="$t('tasks.recurring.never')"
                  class="mr-2"
                />
              </div>

              <div class="flex items-center mb-4 space-x-6">
                <InputElement
                  id="onDate"
                  v-model="form.endCondition"
                  type="radio"
                  value="onDate"
                  :label="$t('tasks.recurring.on')"
                  class=""
                />
                <div class="relative">
                  <InputElement
                    v-model="form.endDate"
                    type="date"
                    :class="['w-10 border rounded pr-10', form.endCondition !== 'onDate' ? 'bg-gray-200' : '']"
                    :disabled="form.endCondition !== 'onDate'"
                  />
                </div>
              </div>

              <div class="flex items-center space-x-2">
                <InputElement
                  id="afterOccurrences"
                  v-model="form.endCondition"
                  type="radio"
                  value="afterOccurrences"
                  :label="$t('tasks.recurring.after')"
                />
                <div class="relative">
                  <InputElement
                    v-model="form.occurrences"
                    type="number"
                    min="1"
                    :class="['w-10 border rounded', form.endCondition !== 'afterOccurrences' ? 'bg-gray-200' : '']"
                    placeholder=" "
                    :disabled="form.endCondition !== 'afterOccurrences'"
                  />
                  <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500">{{ $t('tasks.recurring.occurrences') }}</span>
                </div>
              </div>
            </div>

            <div class="flex justify-end mt-4 space-x-4">
              <AppButton theme="negative" type="button" @click="clearRecurrence">
                {{ $t('tasks.recurring.clear_recurrence') }}
              </AppButton>
              <AppButton theme="primary" type="submit">
                {{ $t('actions.save') }}
              </AppButton>
            </div>
          </form>
        </div>
      </template>
    </DropDown>
  </div>
</template>
