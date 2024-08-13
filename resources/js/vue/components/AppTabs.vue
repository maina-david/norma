<script setup>
import { Link } from '@inertiajs/vue3';
import { watch } from 'vue';
import { useState } from '@/vue/composables/useState';
import LibryoIcon from '@/collaborate/components/LibryoIcon.vue';

const props = defineProps({
  active: { type: String, default: null },
  tabs: { type: Array, required: true },
  noOverflow: { type: Boolean, default: false },
});

const [active, setActive] = useState(props.active || (props.tabs[0]?.id ?? props.tabs[0]));
watch(props, () => setActive(props.active || props.tabs[0]));

function isActive(tab) {
  return active.value === (tab.id ?? tab);
}

function switchTab(event, tab) {
  if (!tab.target) {
    event.preventDefault();
  }

  active.value = tab.id ?? tab;
}
</script>

<template>
  <div class="h-full flex flex-col">
    <div class="border-b border-gray-200 mb-4 notranslate flex-shrink-0">
      <nav class="-mb-px flex space-x-8 w-full overflow-x-auto custom-scroll">
        <template v-for="tab in tabs" :key="tab">
          <Link
            v-if="tab.inertia"
            v-tooltip="tab.tooltip ?? tab.label ?? tab"
            :href="tab.target ?? '#'"
            class="flex items-center whitespace-nowrap py-4 px-2 border-b-2 font-medium text-sm transition-colors ease-in-out duration-200 cursor-pointer"
            :class="{ 'border-primary text-primary': isActive(tab), 'border-transparent text-libryo-gray-500 hover:border-primary hover:text-primary': !isActive(tab) }"
            @click="(e) => switchTab(e, tab)"
          >
            <LibryoIcon v-if="tab.icon" :name="tab.icon" class="mr-3" />

            <span>{{ tab.label ?? tab }}</span>
          </Link>
          
          <a
            v-else
            v-tooltip="tab.tooltip ?? tab.label ?? tab"
            class="flex items-center whitespace-nowrap py-4 px-2 border-b-2 font-medium text-sm transition-colors ease-in-out duration-200 cursor-pointer"
            :class="{ 'border-primary text-primary': isActive(tab), 'border-transparent text-libryo-gray-500 hover:border-primary hover:text-primary': !isActive(tab) }"
            :href="tab.target ?? '#'"
            @click="(e) => switchTab(e, tab)"
          >
            <LibryoIcon v-if="tab.icon" :name="tab.icon" class="mr-3" />

            <span>{{ tab.label ?? tab }}</span>
          </a>
        </template>
      </nav>
    </div>

    <div class="mb-4 flex-grow" :class="{ 'overflow-hidden': noOverflow }">
      <slot :active="active" :activate-tab="setActive" />
    </div>
  </div>
</template>
