<script setup>
import { ref } from 'vue';
import PlannerItem from '@/vue/components/my/actions/PlannerItem.vue';

const props = defineProps({
  category: { type: Object, required: true },
  collapsable: { type: Boolean, default: false },
  bold: { type: Boolean, default: false },
  requirement: { type: Boolean, default: false },
  parent: { type: Boolean, default: false },
});

const open = ref(false);

function handleClick() {
  if (props.collapsable) {
    open.value = !open.value;
  }
}
</script>

<template>
  <div class="w-full" :class="{ 'bg-libryo-gray-200 rounded-lg shadow border border-libryo-gray-200 overflow-hidden': parent }">
    <PlannerItem
      :category="category"
      :collapsable="collapsable"
      :bold="bold"
      :requirement="requirement"
      :open="open"
      @click="handleClick"
    />

    <div v-if="open" class="bg-white rounded-b-lg shadow pb-4 pt-4">
      <div v-if="collapsable && category.children.length > 0" class="flex items-center w-full font-semibold">
        <div class="flex-grow">
          <div class="font-semibold px-6 py-2 ">
            {{ category.label }}
          </div>
        </div>
        <div class="flex-shrink-0 w-40" />
        <div class="flex-shrink-0 w-40 text-center" />
        <div class="flex-shrink-0 w-40 text-center" />
        <div class="flex-shrink-0 w-40 text-center" />
        <div class="w-10" />
      </div>

      <div class="pl-4">
        <template v-for="(child, index) in category.children" :key="child.id">
          <ActionAreaSummaryItem
            v-if="child.sub_label && index > 0 && category.children[index - 1].sub_label !== child.sub_label"
            :category="{ ...child, label: child.sub_label }"
            :requirement="requirement"
            bold
          />

          <div class="pl-4">
            <PlannerItem
              :category="child"
              :requirement="requirement"
            />
          </div>
        </template>
      </div>
    </div>
  </div>
</template>
