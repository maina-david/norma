<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import GroupedAvatars from '@/vue/components/my/users/GroupedAvatars.vue';
import AppIcon from '@/vue/components/AppIcon.vue';
import CountryFlag from '@/vue/components/CountryFlag.vue';
import { getBackgroundColour, getColour, getLabel } from '@/enums/actions/tasks/task-statuses';

const props = defineProps({
  category: { type: Object, required: true },
  collapsable: { type: Boolean, default: false },
  bold: { type: Boolean, default: false },
  requirement: { type: Boolean, default: false },
  open: { type: Boolean, default: false },
});

const emit = defineEmits(['click']);
const shouldBold = computed(() => props.collapsable || props.bold);
const target = computed(() => props.requirement ? `/actions/requirements/tasks/${props.category.id}` : `/actions/topics/tasks/${props.category.id}`);
</script>

<template>
  <div class="px-6 flex items-center" :class="{ 'cursor-pointer py-4' : collapsable, 'py-2': !collapsable, 'font-semibold': shouldBold }" @click.stop="() => emit('click')">
    <div class="flex-grow flex items-center">
      <div v-if="collapsable" class="w-10 flex-shrink-0">
        <AppIcon v-if="category.icon" class="text-primary" :name="category.icon" />
        <CountryFlag v-if="category.flag" class="h-6 2-6 rounded-full overflow-hidden" :country-code="category.flag" />
      </div>

      <div>
        <Link v-if="!shouldBold" :href="target" class="hover:text-primary">
          {{ category.label }}
        </Link>
        <div v-else>
          {{ category.label }}
        </div>
      </div>
    </div>
    <div class="flex-shrink-0 w-40 -my-2">
      <GroupedAvatars :users="category.assignees" />
    </div>
    <div class="flex-shrink-0 w-40 text-center">
      <span v-if="!requirement || collapsable">
        {{ category.references_count }}
      </span>
    </div>
    <div class="flex-shrink-0 w-40 text-center">
      {{ category.tasks_count }}
    </div>
    <div class="flex-shrink-0 w-40 text-center">
      <span class="rounded-full px-4 py-1" :class="`${getBackgroundColour(category.status)} ${getColour(category.status)}`">
        {{ getLabel(category.status) }}
      </span>
    </div>
    <div class="w-10 text-primary flex-shrink-0">
      <AppIcon v-show="collapsable && !open" name="angle-down" />
      <AppIcon v-show="collapsable && open" name="angle-up" />
    </div>
  </div>
</template>
