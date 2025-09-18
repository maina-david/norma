<script setup>
import { ref, watch } from 'vue';
import { RiskOfNonCompliance, getLabel, getColor } from '@/enums/actions/checklists/risk-of-non-compliance';
import InputElement from '@/vue/components/InputElement.vue';
import AppIcon from '@/vue/components/AppIcon.vue';
import ChecklistLayout from '@/vue/components/my/actions/checklists/ChecklistLayout.vue';
import { useAxios } from '@/vue/composables/useAxios';

defineOptions({ layout: [ChecklistLayout] });
const axios = useAxios();
const collapsable = ref(true);
const openStates = ref({});
const loading = ref(false);
const items = ref([]);

function toggleOpen(id) {
  openStates.value[id] = !openStates.value[id];
}

function fetchItems() {
  loading.value = true;
  return axios.get('/actions/checklist/areas')
    .then(({ data }) => {
      const categoriesMap = {};

      const fetchedData = data.data;

      fetchedData.forEach((item) => {
        if (!categoriesMap[item.subject_label]) {
          categoriesMap[item.subject_label] = {
            id: Object.keys(categoriesMap).length + 1,
            category: item.subject_label,
            icon: item.subject_icon,
            subCategories: [],
          };
        }

        const category = categoriesMap[item.subject_label];
        const subCategory = category.subCategories.find((sub) => sub.subCategory === item.control_label);

        if (!subCategory) {
          category.subCategories.push({
            id: category.subCategories.length + 1,
            subCategory: item.control_label,
            actions: [
              {
                id: item.id,
                text: item.title,
                riskOfNonCompliance: item.riskOfNonCompliance ?? 4,
                answered: '',
                nextReview: '',
              },
            ],
          });
        } else {
          subCategory.actions.push({
            id: item.id,
            text: item.title,
            riskOfNonCompliance: item.riskOfNonCompliance ?? 4,
            answered: '',
            nextReview: '',
          });
        }
      });

      items.value = Object.values(categoriesMap);

      items.value.forEach((category) => {
        category.subCategories.forEach((subCategory) => {
          subCategory.actions.forEach((action) => {
            watch(() => action.riskOfNonCompliance, () => {
              updateRiskOfNonCompliance(action);
            });
          });
        });
      });
    })
    .finally(() => {
      loading.value = false;
    });
}

fetchItems();

async function updateRiskOfNonCompliance(action) {
  try {
    await axios.put(`/actions/${action.id}/checklist/update`, {
      riskOfNonCompliance: action.riskOfNonCompliance,
    });
  } catch (error) {
    console.error('Error updating risk of non-compliance:', error);
  }
}

function toggleChildCheckboxes(parentId, isChecked) {
  const category = items.value.find((item) => item.id === parentId);
  if (category) {
    category.subCategories.forEach((subCategory) => {
      subCategory.checked = isChecked;
      subCategory.actions.forEach((action) => {
        action.checked = isChecked;
      });
    });
  }
}

function toggleSubCategoryCheckboxes(categoryId, subCategoryId, isChecked) {
  const category = items.value.find((item) => item.id === categoryId);
  if (category) {
    const subCategory = category.subCategories.find((sub) => sub.id === subCategoryId);
    if (subCategory) {
      subCategory.actions.forEach((action) => {
        action.checked = isChecked;
      });
    }
  }
}
</script>

<template>
  <div v-loading="loading" class="space-y-3 pl-2 mt-4">
    <!-- Header Row -->
    <div class="flex font-semibold mb-4">
      <!-- Action Area Header -->
      <div class="flex justify-start ml-2 w-1/4">
        <div>
          {{ $t('actions.checklist.action_area') }}
        </div>
      </div>

      <!-- Risk of Non-Compliance Header -->
      <div class="flex justify-center w-1/6 whitespace-nowrap">
        <div>{{ $t('actions.checklist.risk_of_non_compliance') }}</div>
      </div>

      <!-- Risk Level Headers -->
      <div class="flex justify-center w-2/6">
        <div class="flex space-x-4">
          <span :class="`px-1 py-0.5 rounded ${getColor(RiskOfNonCompliance.low)}`">{{ getLabel(RiskOfNonCompliance.low) }}</span>
          <span :class="`px-1 py-0.5 rounded ${getColor(RiskOfNonCompliance.medium)}`">{{ getLabel(RiskOfNonCompliance.medium) }}</span>
          <span :class="`px-1 py-0.5 rounded ${getColor(RiskOfNonCompliance.high)}`">{{ getLabel(RiskOfNonCompliance.high) }}</span>
          <span :class="`px-1 py-0.5 rounded ${getColor(RiskOfNonCompliance.noAnswer)}`">{{ getLabel(RiskOfNonCompliance.noAnswer) }}</span>
        </div>
      </div>

      <!-- Answered Header -->
      <div class="flex justify-center w-1/12" />
      <div class="flex justify-center w-1/6">
        <div>{{ $t('actions.checklist.answered') }}</div>
      </div>
      <!-- Next Review Header -->
      <div class="flex justify-center w-1/6">
        <div>{{ $t('actions.checklist.next_review') }}</div>
      </div>
    </div>

    <div v-for="(item, index) in items" :key="index" class="border-norma-gray-200 rounded mb-2">
      <!-- Row Header Start -->
      <div class="bg-norma-gray-200 rounded-lg shadow border border-norma-gray-200 flex flex-row" @click="toggleOpen(item.id)">
        <div class="flex justify-between w-full p-4">
          <div class="flex items-center space-x-4">
            <InputElement v-model="item.checked" type="checkbox" @change="toggleChildCheckboxes(item.id, item.checked)" />
            <AppIcon :name="item.icon" class="text-primary" />
            <span class="font-semibold">
              {{ item.category }}
            </span>
          </div>
          <div class="text-primary flex-shrink-0 cursor-pointer">
            <AppIcon v-if="collapsable" :name="openStates[item.id] ? 'angle-up' : 'angle-down'" />
          </div>
        </div>
      </div>
      <!-- Row Header End -->

      <!-- Collapsible Section -->
      <transition>
        <div v-show="openStates[item.id]">
          <div v-for="(subCategory, subIndex) in item.subCategories" :key="subIndex">
            <div class="flex font-semibold p-4">
              <div class="flex justify-start w-full">
                <div class="flex items-center">
                  <InputElement v-model="subCategory.checked" type="checkbox" class="mt-0.5" @change="toggleSubCategoryCheckboxes(item.id, subCategory.id, subCategory.checked)" />
                  <span>{{ subCategory.subCategory }}</span>
                </div>
              </div>
            </div>
            <div v-for="(action, actionIndex) in subCategory.actions" :key="actionIndex" class="flex p-4">
              <div class="flex flex-row justify-start w-full">
                <div class="flex items-center mr-8 w-[390px] flex-shrink-0">
                  <InputElement v-model="action.checked" type="checkbox" class="mt-0.5" />
                  <span>{{ action.text }}</span>
                </div>
                <div class="flex flex-row items-center space-x-12 mr-12">
                  <input
                    v-model="action.riskOfNonCompliance"
                    type="radio"
                    :value="RiskOfNonCompliance.low"
                    :checked="action.riskOfNonCompliance === RiskOfNonCompliance.low"
                    class="mt-1 text-primary"
                  >
                  <input
                    v-model="action.riskOfNonCompliance"
                    type="radio"
                    :value="RiskOfNonCompliance.medium"
                    :checked="action.riskOfNonCompliance === RiskOfNonCompliance.medium"
                    class="mt-1 text-primary"
                  >
                  <input
                    v-model="action.riskOfNonCompliance"
                    type="radio"
                    :value="RiskOfNonCompliance.high"
                    :checked="action.riskOfNonCompliance === RiskOfNonCompliance.high"
                    class="mt-1 text-primary"
                  >
                  <input
                    v-model="action.riskOfNonCompliance"
                    type="radio"
                    :value="RiskOfNonCompliance.noAnswer"
                    :checked="action.riskOfNonCompliance === RiskOfNonCompliance.noAnswer"
                    class="mt-1 text-primary"
                  >
                </div>
                <div class="flex flex-row items-center space-x-4">
                  <div>
                    <span class="font-italic hover:underline text-primary whitespace-nowrap cursor-pointer">Mark as Unchanged</span>
                  </div>
                  <div class="flex flex-row items-center space-x-14 ml-2">
                    <div>
                      <span>{{ action.answered }}</span>
                    </div>
                    <div>
                      <span>{{ action.nextReview }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </transition>
    </div>
  </div>
</template>
