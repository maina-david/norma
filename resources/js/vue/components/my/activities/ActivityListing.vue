<script setup>
import { ref } from 'vue';
import { useAxios } from '@/vue/composables/useAxios';
import UserAvatar from '@/vue/components/my/users/UserAvatar.vue';
import { ActivityIcons, DefaultIcon } from '@/vue/components/my/activities/icons';
import AppIcon from '@/vue/components/AppIcon.vue';
import EmptyState from '@/vue/components/EmptyState.vue';

const props = defineProps({
  value: { type: String, default: '' },
  relatedId: { type: [String, Number], default: null },
  relation: { type: String, default: null },
});

const axios = useAxios();
const activities = ref([]);
const loading = ref(false);

function fetchItems() {
  loading.value =true;
  axios.get(`/activities/${props.relation}/${props.relatedId}`)
    .then(({ data }) => data)
    .then(({ data }) => {
      activities.value = [...data];
    })
    .finally(() => {
      loading.value = false;
    });
}

function getIcon(type) {
  return ActivityIcons[type] ?? DefaultIcon;
}

fetchItems();
</script>

<template>
  <div v-loading="loading" class="min-h-20">
    <ul v-if="activities.length > 0" role="list" class="divide-y divide-norma-gray-200">
      <li v-for="activity in activities" :key="activity.id" class="py-4">
        <div class="flex space-x-3">
          <div v-if="activity.user">
            <UserAvatar :user="activity.user" />
          </div>

          <div class="flex-1 space-y-1">
            <div class="flex items-center justify-between">
              <h3 class="text-sm font-medium">
                {{ activity.user?.name ?? '' }}
              </h3>
              <p class="text-sm text-norma-gray-500">
                {{ $format.dateDiff(activity.created_at) }}
              </p>
            </div>
            <div>
              <p class="text-sm text-norma-gray-500">
                <span class="inline-block mr-4">
                  <AppIcon :name="getIcon(activity.activity_type)" />
                </span>
                {{ activity.description }}
              </p>
            </div>
          </div>
        </div>
      </li>
    </ul>

    <EmptyState v-if="!loading && activities.length < 1" :title="$t('interface.activity_no_activities')" icon="analytics" />
  </div>
</template>
