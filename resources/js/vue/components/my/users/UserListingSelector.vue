<script setup>
import { computed, ref } from 'vue';
import AppIcon from '@/vue/components/AppIcon.vue';
import InputElement from '@/vue/components/InputElement.vue';
import { useAxios } from '@/vue/composables/useAxios';
import UserAvatar from '@/vue/components/my/users/UserAvatar.vue';
import useRootPersist from '@/vue/composables/useRootPersist';

const props = defineProps({
  libryo: { type: Boolean, default: false },
  multiple: { type: Boolean, default: false },
});

const axios = useAxios();
const value = defineModel();
const search = ref('');

const checkedUsers = computed(() => {
  if (!value.value) {
    return [];
  }

  return props.multiple ? [...value.value] : [value.value];
});
const params = props.libryo ? { libryo: true } : {};

const { stored, loading } = useRootPersist({
  key: 'user_listing_selector',
  defaultValue: [],
  fetchData() {
    return axios.get('/organisation/users', { params })
      .then(({ data }) => data)
      .then(({ data }) => data);
  },
});

const filtered = computed(() => {
  if (search.value.length < 1) {
    return stored.value;
  }

  return stored.value.filter((item) => item.name.toLowerCase().replace(/\s+/, '').includes(search.value.toLowerCase()));
});

function selectUser(selected) {
  if (!props.multiple) {
    value.value = selected.id;
    return;
  }

  const copy = Array.isArray(value.value) ? [...value.value] : [value.value];
  const index = copy.indexOf(selected.id);

  if (index !== -1) {
    copy.splice(index, 1);
  } else {
    copy.push(selected.id);
  }

  value.value = [...copy];
}
</script>

<template>
  <div v-loading="loading">
    <div class="bg-white flex flex-col px-1 pt-1 pb-1 h-full max-h-[20rem] min-w-64">
      <div class="flex-shrink-0 px-2 flex items-center relative w-full mb-2">
        <div class="absolute left-0 top-0 bottom-0 flex items-center pl-6 pt-1">
          <AppIcon name="search" size="4" />
        </div>
        <div class="flex-grow">
          <InputElement v-model="search" class="pl-10" name="search" placeholder="Search" />
        </div>
      </div>

      <div class="flex-grow overflow-y-auto custom-scroll">
        <div
          v-for="user in filtered"
          :key="user.id"
          class="flex items-center hover:bg-libryo-gray-100 px-3 py-1 rounded-lg cursor-pointer"
          @click.stop="() => selectUser(user)"
        >
          <UserAvatar class="flex-shrink-0" size="4" :user="user" />

          <div class="ml-2 flex-grow whitespace-nowrap">
            {{ user.name }}
          </div>

          <div class="w-2 flex-shrink-0">
            <AppIcon v-if="checkedUsers.includes(user.id)" name="check" size="4" class="text-primary" />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
