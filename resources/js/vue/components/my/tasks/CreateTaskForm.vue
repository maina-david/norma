<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import AppButton from '@/vue/components/AppButton.vue';
import { TaskStatus } from '@/enums/actions/tasks/task-statuses';
import InputElement from '@/vue/components/InputElement.vue';
import SelectElement from '@/vue/components/SelectElement.vue';
import { TaskPriority } from '@/enums/actions/tasks/task-priorities';
import WysiwygEditor from '@/vue/components/WysiwygEditor.vue';
import { useAxios } from '@/vue/composables/useAxios';
import impactRange from '@/vue/components/my/tasks/impact-range';

const props = defineProps({
  reference: { type: Boolean, default: false },
  type: { type: String, required: true },
  typeId: { type: [String, Number], required: true },
  module: { type: String, default: 'actions' },
  actionAreas: { type: Array, default: () => [] },
  value: { type: Object, default: null },
});

const emit = defineEmits(['create']);
const { t } = useI18n({ useScope: 'global' });
const axios = useAxios();
const page = usePage();
const loading = ref(false);
const errors = ref({});

const form = ref({});

const mappedAreas = computed(() => props.actionAreas.map((item) => ({ label: item.title, value: item.id })));
const priorities = TaskPriority.forSelector();

function resetForm() {
  form.value = {
    title: null,
    description: null,
    task_status: TaskStatus.notStarted,
    priority: TaskPriority.medium,
    impact: null,
    due_on: null,
    taskable_type: props.type,
    taskable_id: props.typeId,
    ...(props.value ?? {}),
    copy: !page.props.stream.single,
  };
  loading.value = false;
}

resetForm();

function handleSubmit() {
  loading.value = true;
  return axios.post('/actions/tasks', { ...form.value })
    .then(({ data }) => data)
    .then(({ data }) => {
      resetForm();
      emit('create');
      window.toast.success({ message: t('actions.saved_successfully') });

      return data.hash_id;
    })
    .catch(({ response }) => {
      if (response.status === 422) {
        errors.value = response.data.errors;
      }
    })
    .finally(() => {
      loading.value = false;
    });
}

function saveAndRedirect() {
  handleSubmit()
    .then((id) => {
      window.location.href = `/${props.module}/${id}`;
    });
}
</script>

<template>
  <form v-loading="loading" action="#" @submit.prevent="handleSubmit">
    <div>
      <InputElement
        v-model="form.title"
        :errors="errors.title ?? []"
        :label="$t('interface.title')"
        required
      />

      <SelectElement
        v-model="form.impact"
        :errors="errors.impact ?? []"
        :label="$t('tasks.impact')"
        :options="impactRange"
      />

      <InputElement
        v-model="form.due_on"
        :errors="errors.due_on ?? []"
        type="date"
        :label="$t('tasks.due_on')"
      />

      <SelectElement
        v-model="form.priority"
        :errors="errors.priority ?? []"
        :label="$t('tasks.priority')"
        :options="priorities"
      />

      <SelectElement
        v-if="reference"
        v-model="form.action_area_id"
        :errors="errors.action_area_id ?? []"
        :label="$t('actions.action_area.action_area')"
        :options="mappedAreas"
        required
      />

      <WysiwygEditor
        v-model="form.description"
        :errors="errors.description ?? []"
        type="textarea"
        :label="$t('interface.description')"
        class="norma-editor-minimal"
      />
    </div>

    <div v-if="reference && !form.id" class="mt-4">
      <InputElement
        v-model="form.copy"
        v-tooltip="$t(page.props.stream.single ? 'tasks.create_copies' : 'tasks.create_copies_multi_stream')"
        :disabled="!page.props.stream.single"
        type="checkbox"
        :label="$t('tasks.create_copies')"
      />
    </div>

    <div class="flex justify-end mt-4 space-x-4">
      <AppButton theme="primary" type="submit">
        {{ $t('actions.save') }}
      </AppButton>

      <AppButton theme="primary" type="button" @click.prevent="saveAndRedirect">
        {{ $t('actions.save_and_edit') }}
      </AppButton>
    </div>
  </form>
</template>
