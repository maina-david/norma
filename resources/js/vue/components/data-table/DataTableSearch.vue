<script setup>
import InputElement from '@/vue/components/InputElement.vue';
import AppIcon from '@/vue/components/AppIcon.vue';

const emit = defineEmits(['search']);
const search = defineModel();
function handleSearch() {
  emit('search', search);
}
function clearSearch() {
  search.value = '';
  emit('search', search);
}
</script>

<template>
  <form action="#" @submit.prevent="handleSearch">
    <div class="flex items-center px-2">
      <div class="min-w-64 flex items-center relative">
        <div class="flex-grow">
          <InputElement
            v-model="search"
            name="search"
            label=""
            placeholder="Search"
            class="pr-12"
          />
        </div>
        <div v-show="search.length > 0" class="absolute h-full flex items-center right-12">
          <button type="button" class="px-2 py-2" @click.prevent="clearSearch">
            <AppIcon name="times" size="4" />
          </button>
        </div>
      </div>

      <button type="submit" class="bg-dark py-2 px-3 -ml-11 text-white rounded-r-md relative">
        <AppIcon name="search" />
      </button>
    </div>
  </form>
</template>
