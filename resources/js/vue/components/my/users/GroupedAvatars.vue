<script setup>
import { computed } from 'vue';
import UserAvatar from '@/vue/components/my/users/UserAvatar.vue';

const props = defineProps({
  dimensions: { type: [String, Number], default: 8 },
  limit: { type: [String, Number], default: 3 },
  users: { type: Array, required: true },
});

const width = Number(props.dimensions) - 2;

const uniqueUsers = computed(() => {
  const unique = {};

  props.users.forEach((user) => {
    unique[user.id] = user;
  });

  return Object.values(unique);
});

const reminder = uniqueUsers.value.length - props.limit;
const usable = computed(() => [...uniqueUsers.value].splice(0, props.limit));
</script>

<template>
  <div class="flex items-center">
    <div v-for="user in usable" :key="user.id" :class="`w-${width}`">
      <UserAvatar :user="user" :dimensions="dimensions" />
    </div>

    <div v-if="reminder > 0" :class="`w-${dimensions} h-${dimensions}text-white bg-norma-gray-400 rounded-full flex items-center justify-center ring-1 ring-white`">
      <span>+{{ reminder }}</span>
    </div>
  </div>
</template>
