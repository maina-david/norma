<script setup>
import { ref } from 'vue';
import AppIcon from '@/vue/components/AppIcon.vue';
import CreateTaskButton from '@/vue/components/my/tasks/CreateTaskButton.vue';
import { TaskTypes } from '@/enums/actions/tasks/task-types';
import { useAxios } from '@/vue/composables/useAxios';

const props = defineProps({
  referenceId: { type: Number, required: true },
  actionAreas: { type: Array, default: () => [] },
});
const emit = defineEmits(['create']);
const axios = useAxios();
const extracts = ref([]);
const loading = ref(true);
const typeRequirement = TaskTypes.requirements;

axios.get(`/references/${props.referenceId}/extracts`)
  .then(({ data }) => data)
  .then(({ data }) => {
    extracts.value = data;
  })
  .finally(() => {
    loading.value = false;
  });

function handleCreate(item) {
  item.attached = true;

  emit('create');
}

</script>

<template>
  <div>
    <div v-loading="loading" class="divide-y divide-libryo-gray-100 py-4">
      <div v-for="item in extracts" :key="item.id" class="flex py-2">
        <div class="px-6 flex-grow">
          <div class="font-semibold">
            {{ item.content }}
          </div>
          <div class="italic text-xs pt-1">
            {{ $t('tasks.libryo_suggested_tasks') }}
          </div>
        </div>
        <div class="pr-4">
          <div v-if="item.attached" class="h-8 w-8 mt-2">
            <AppIcon name="check" />
          </div>

          <CreateTaskButton
            v-else
            icon-only
            reference
            :type-id="referenceId"
            :type="typeRequirement"
            :action-areas="actionAreas"
            :value="{ title: item.content, reference_content_extract_id: item.id, action_area_id: actionAreas[0]?.id }"
            @create="() => handleCreate(item)"
          />
        </div>
      </div>
    </div>
  </div>
</template>
