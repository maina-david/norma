<script setup>
import { computed, ref } from 'vue';
import AppIcon from '@/vue/components/AppIcon.vue';
import InputElement from '@/vue/components/InputElement.vue';
import SelectElement from '@/vue/components/SelectElement.vue';
import AppButton from '@/vue/components/AppButton.vue';

const props = defineProps({
  filters: { type: Array, required: true },
});

const emit = defineEmits(['apply', 'clear']);
const open = ref(false);
const filtered = computed(() => false);

function apply() {
  emit('apply');
}

function clear() {
  props.filters.forEach((item) => {
    item.value = item.multiple ? [] : null;
  });

  emit('apply');
}
</script>

<template>
  <div class="lg:bg-white lg:shadow-sm lg:px-4 mr-2 rounded-md" :class="{ 'bg-white shadow-sm px-4': open }">
    <div class="pr-2 flex justify-center">
      <div>
        <button
          class="lg:hidden inline-flex items-center rounded-md font-semibold text-xs text-center px-2 border focus:outline-none"
          :class="{ 'flex-col py-4': !open, 'py-1 my-8': open, 'bg-primary border-primary text-white hover:bg-primary hover:text-white active:border-primary': filtered, 'bg-white border-dark text-dark hover:bg-dark hover:text-white active:border-dark': !filtered }"
          @click="open = !open"
        >
          <span :class="{ 'pb-4': !open, 'pl-2 pr-4': open }">
            <AppIcon name="filter" size="4" />
          </span>

          <span v-if="!open" class="flex justify-center pt-4 border-t border-libryo-gray-300">
            <span class="lg:hidden font-semibold flex-col flex">
              <span>O</span>
              <span>P</span>
              <span>E</span>
              <span>N</span>
              <span>&nbsp;</span>
              <span>F</span>
              <span>I</span>
              <span>L</span>
              <span>T</span>
              <span>E</span>
              <span>R</span>
              <span>S</span>
            </span>
          </span>

          <span v-else class="lg:hidden tracking-widest font-semibold mr-4 border-l border-libryo-gray-300 pl-4">
            CLOSE FILTERS
          </span>
        </button>
      </div>
    </div>

    <div class="lg:block w-96 sm:w-60 max-w-screen-75 pt-2" :class="{ hidden: !open, block: open }">
      <div class="flex justify-end items-center space-x-4 mt-2">
        <AppButton @click="clear">
          Clear
        </AppButton>
        <AppButton theme="primary" class="bg-primary text-white" @click="apply">
          Apply
        </AppButton>
      </div>

      <template v-for="filter in filters" :key="filter.name">
        <label class="text-sm font-medium text-libryo-gray-700 block mt-4 flex items-center">{{ $t(filter.label) }}</label>
        <component :is="filter.component()" v-if="filter.component" v-model="filter.value" :multiple="filter.multiple" />
        <SelectElement v-else-if="filter.type === 'select'" v-model="filter.value" :multiple="filter.multiple" :options="filter.options" />
        <InputElement v-else v-model="filter.value" :type="filter.type" />
      </template>

      <div class="flex justify-end items-center space-x-4 mt-4 pb-6">
        <AppButton @click="clear">
          Clear
        </AppButton>
        <AppButton theme="primary" class="bg-primary text-white" @click="apply">
          Apply
        </AppButton>
      </div>
    </div>
  </div>
</template>
