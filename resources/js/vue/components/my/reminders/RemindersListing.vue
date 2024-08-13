<script setup>
import { ref } from 'vue';
import AppModal from '@/vue/components/AppModal.vue';
import AppButton from '@/vue/components/AppButton.vue';
import AppIcon from '@/vue/components/AppIcon.vue';
import ReminderForm from '@/vue/components/my/reminders/ReminderForm.vue';
import EmptyState from '@/vue/components/EmptyState.vue';
import { useAxios } from '@/vue/composables/useAxios';
import ConfirmButton from '@/vue/components/ConfirmButton.vue';

const props = defineProps({
  relatedId: { type: [String, Number], default: null },
  relation: { type: String, default: null },
});

const reminders = ref([]);

const loading = ref(false);
const axios = useAxios();

function fetchReminders() {
  loading.value = true;

  axios.get(`/reminders/${props.relation}/${props.relatedId}`)
    .then(({ data }) => data)
    .then(({ data }) => {
      reminders.value = [...data];
    })
    .finally(() => {
      loading.value = false;
    });
}

function handleClose(toggle) {
  toggle();
  fetchReminders();
}

fetchReminders();
</script>

<template>
  <div>
    <div class="flex justify-end">
      <AppModal>
        <template #trigger="{ toggle }">
          <AppButton theme="primary" @click="toggle">
            <AppIcon name="plus" size="3" />
            <span class="ml-3">{{ $t('notify.reminder.create_reminder') }}</span>
          </AppButton>
        </template>

        <template #default="{ toggle, visible }">
          <div v-if="visible" class="max-w-screen-75 bg-white rounded-lg pt-2 pb-8 px-8">
            <div class="mt-4 font-semibold">
              {{ $t('notify.reminder.create_reminder') }}
            </div>

            <ReminderForm :relation="relation" :related-id="relatedId" @save="() => handleClose(toggle)" />
          </div>
        </template>
      </AppModal>
    </div>

    <EmptyState v-if="!loading && reminders.length < 1" :title="$t('notify.reminder.no_reminders_added')" icon="alarm-clock" />

    <ul role="list" class="divide-y divide-libryo-gray-200 mt-4">
      <li v-for="reminder in reminders" :key="reminder.id">
        <div class="block bg-white">
          <div class="px-4 py-4 flex items-center sm:px-6">
            <div class="min-w-0 flex-1 sm:flex sm:items-center sm:justify-between">
              <div class="mt-4 shrink-0 sm:mt-0 sm:ml-5">
                <div class="text-libryo-gray-500">
                  {{ $format.date(reminder.remind_on) }}
                </div>
              </div>
              <div class="shrink-0 ml-5">
                <ConfirmButton
                  :title="$t('actions.delete')"
                  method="DELETE"
                  :message="$t('actions.confirmation')"
                  :target="`/reminders/${reminder.id}`"
                  @confirm="fetchReminders"
                >
                  <AppIcon name="trash-alt" />
                </ConfirmButton>
              </div>
            </div>
          </div>
        </div>
      </li>
    </ul>
  </div>
</template>
