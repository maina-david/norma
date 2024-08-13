<script setup>
import { useI18n } from 'vue-i18n';
import { inject } from 'vue';
import AppTabs from '@/vue/components/AppTabs.vue';
import CommentListing from '@/vue/components/my/comments/CommentListing.vue';
import ActivityListing from '@/vue/components/my/activities/ActivityListing.vue';
import RemindersListing from '@/vue/components/my/reminders/RemindersListing.vue';

const { t } = useI18n({ useScope: 'global' });
const orgHasModule = inject('orgHasModule');

defineProps({
  row: { type: Object, required: true },
  rowIndex: { type: Number, required: true },
});

const tabs = [];

if (orgHasModule('comments')) {
  tabs.push({ id: 'comments',  label: t('comments.comments'),  icon: 'comments' });
}
tabs.push({ id: 'activities',  label: t('tasks.activity'),  icon: 'analytics' });
tabs.push({ id: 'reminders',  label: t('reminders.reminders'),  icon: 'alarm-clock' }); // reminders/for/task/
</script>

<template>
  <AppTabs :tabs="tabs" :active="tabs[0].id">
    <template #default="{ active }">
      <KeepAlive>
        <CommentListing v-if="active === 'comments'" relation="task" :related-id="row.id" />
      </KeepAlive>

      <KeepAlive>
        <ActivityListing v-if="active === 'activities'" relation="task" :related-id="row.id" />
      </KeepAlive>

      <KeepAlive>
        <RemindersListing v-if="active === 'reminders'" relation="task" :related-id="row.id" />
      </KeepAlive>
    </template>
  </AppTabs>
</template>
