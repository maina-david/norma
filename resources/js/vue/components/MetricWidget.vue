<script setup>
import { Link } from '@inertiajs/vue3';
import AppIcon from '@/vue/components/AppIcon.vue';
import AppCard from '@/vue/components/AppCard.vue';

defineProps({
  colour: { type: String, default: '' },
  icon: { type: String, default: null },
  title: { type: String, required: true },
  value: { type: String, required: true  },
  target: { type: String, default: null  },
});
</script>

<template>
  <AppCard>
    <component :is="target ? Link : 'div'" :class="{ 'cursor-pointer hover:text-primary': target }" :href="target">
      <dt>
        <p class="truncate text-sm font-semibold text-norma-gray-500">
          {{ title }}
        </p>
      </dt>
      <dd class=" flex items-baseline">
        <p class="text-3xl font-semibold" :class="colour">
          <span>{{ value }}</span>
          <AppIcon v-if="icon" class="ml-3 text-norma-gray-500" :name="icon" size="8" />
        </p>
      </dd>
    </component>
  </AppCard>
</template>
