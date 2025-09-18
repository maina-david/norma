<script setup>
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { usePage } from '@inertiajs/vue3';
import InputElement from '@/vue/components/InputElement.vue';
import SelectElement from '@/vue/components/SelectElement.vue';
import AppButton from '@/vue/components/AppButton.vue';
import { useAxios } from '@/vue/composables/useAxios';

const { t } = useI18n({ useScope: 'global' });
const axios = useAxios();
const page = usePage();
const props = defineProps({
  relatedId: { type: [String, Number], default: null },
  relation: { type: String, default: null },
  reply: { type: Boolean, default: false },
});

const form = ref({ remind_on_date: '', remind_on_time: '', remind_whom: null, notification_config: [] });
const errors = ref({});
const loading = ref(false);
const emit = defineEmits(['save']);

const notifiable = computed(() => {
  if (props.relation === 'task') {
    return [
      { label: t('tasks.task.task_user.1'), value:  1 },
      { label: t('tasks.task.task_user.2'), value:  2 },
      { label: t('tasks.task.task_user.3'), value:  3 },
    ];
  }

  const current = [
    { label: t('notify.reminder.me'), value: 'self' },
    { label: page.props.stream.org_title, value: 'organisation' },
  ];

  if (page.props.stream.single) {
    current.push({ label: page.props.stream.title, value: 'norma' });
  }

  return current;
});

function handleSave() {
  loading.value = true;

  axios.post(`/reminders/${props.relation}/${props.relatedId}`, form.value)
    .then(() => {
      form.value = { remind_on_date: '', remind_on_time: '', remind_whom: null, notification_config: [] };
      window.toast.success({ message: t('actions.saved_successfully') });
      emit('save');
    })
    .catch(({ response }) => {
      errors.value = response.data.errors ?? [];
    })
    .finally(() => {
      loading.value = false;
    });
}
</script>

<template>
  <div v-loading="loading">
    <form action="#" @submit.prevent="handleSave">
      <div class="grid grid-cols-3 gap-4">
        <div class="col-span-2">
          <InputElement
            v-model="form.remind_on_date"
            required
            :label="$t('interface.date')"
            type="date"
            :errors="errors.remind_on_date ?? []"
          />
        </div>
        <div>
          <InputElement
            v-model="form.remind_on_time"
            required
            :label="$t('interface.time')"
            type="time"
            :errors="errors.remind_on_time ?? []"
          />
        </div>
      </div>

      <div>
        <SelectElement
          v-if="relation === 'task'"
          v-model="form.notification_config"
          :label="$t('notify.reminder.remind_whom')"
          :options="notifiable"
          :errors="errors.notification_config ?? []"
          multiple
        />

        <SelectElement
          v-else
          v-model="form.remind_whom"
          :label="$t('notify.reminder.remind_whom')"
          :options="notifiable"
          :errors="errors.remind_whom ?? []"
        />
      </div>

      <div class="flex justify-end">
        <AppButton theme="primary" type="submit" class="mt-4 bg-primary text-white">
          {{ $t('actions.save') }}
        </AppButton>
      </div>
    </form>
  </div>
</template>

<!--@php-->
<!--use App\Enums\Tasks\TaskUser;-->
<!--@endphp-->

<!--<input type="hidden" name="remindable_type" value="{{ $remindableType }}" />-->
<!--@if ($remindableType !== 'task' || ($remindableType === 'task' && !$isCreateForm))-->
<!--<input type="hidden" name="remindable_id" value="{{ $remindableId }}" />-->
<!--@endif-->

<!--@if ($remindableType !== 'task')-->
<!--<x-ui.input :value="old('title', $resource->title ?? '')"-->
<!--            name="title"-->
<!--            label="{{ __('interface.title') }}" />-->
<!--<x-ui.input type="textarea" :value="old('description', $resource->description ?? '')"-->
<!--            name="description"-->
<!--            label="{{ __('interface.description') }}" />-->
<!--@endif-->

<!--<div class="flex flex-row">-->
<!--<div class="w-44">-->
<!--  <x-ui.input type="date"-->
<!--              :value="old('remind_on_date', isset($resource) ? ($resource->remind_on_date?->format('Y-m-d') ?? '') : '')"-->
<!--              name="remind_on_date"-->
<!--              label="{{ __('notify.reminder.remind_on_date') }}" />-->

<!--</div>-->
<!--<div class="w-32 ml-5">-->

<!--  <x-ui.input type="time"-->
<!--              :value="old('remind_on_time', $resource->remind_on_time ?? '08:00')"-->
<!--              name="remind_on_time"-->
<!--              label="{{ __('notify.reminder.remind_on_time') }}" />-->
<!--</div>-->

<!--</div>-->

<!--@if ($remindableType === 'task')-->
<!--<x-ui.input type="select"-->
<!--            multiple-->
<!--            :options="TaskUser::lang()"-->
<!--            :value="$remindWhomValue"-->
<!--            name="notification_config[]"-->
<!--            label="{{ __('notify.reminder.remind_whom') }}" />-->
<!--@else-->
<!--<x-ui.input type="select"-->
<!--            :options="$whomOptions"-->
<!--            :value="old('remind_whom', $remindWhomValue)"-->
<!--            name="remind_whom"-->
<!--            label="{{ __('notify.reminder.remind_whom') }}" />-->

<!--@endif-->
