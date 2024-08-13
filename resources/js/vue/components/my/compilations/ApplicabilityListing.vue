<script setup>
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useAxios } from '@/vue/composables/useAxios';

const props = defineProps({
  referenceId: { type: [String, Number], required: true },
});

const { t } = useI18n({ useScope: 'global' });

const axios = useAxios();
const loading = ref(false);
const canManageApplicability = ref(false);
const locations = ref([]);
const questions = ref([]);
const libryo = ref({});
const categories = ref([]);

function fetchContent() {
  loading.value = true;
  axios.get(`/references/${props.referenceId}/applicability`)
    .then(({ data }) => data)
    .then(({ data }) => {
      locations.value = data.locations;
      libryo.value = data.libryo;
      canManageApplicability.value = data.canManageApplicability;
      questions.value = data.questions;
      categories.value = data.categories;
    })
    .finally(() => {
      loading.value = false;
    });
}

fetchContent();
</script>

<template>
  <div v-loading="loading">
    <div v-show="!loading">
      <div class="wysiwyg-content libryo-legislation">
        <div class="mt-4 font-semibold text-lg">
          <p>
            {{ t('compilation.context_question.requirement_reason') }}
          </p>
        </div>

        <div>
          <span class="text-libryo-gray-800 font-semibold text-sm">
            {{ t('compilation.context_question.requirement_reason_location', { libryo: libryo.title }) }}
          </span>

          <div class="mt-0">
            <div v-for="location in locations" :key="location.id">
              <span class="text-sm">
                {{ location }}
              </span>
            </div>
          </div>
        </div>
      </div>

      <div v-if="questions.length > 0" class="mt-4">
        <span class="text-libryo-gray-800 font-semibold text-sm">
          {{ t('compilation.context_question.requirement_reason_context') }}
        </span>
        <div class="mt-0 mb-8">
          <div v-for="question in questions" :key="question.id">
            <div class="text-sm">
              <div v-if="canManageApplicability">
                <a :href="`/applicability/${question.hash_id}/${libryo.hash_id}`" class="space-x-2 text-primary-lighter">
                  {{ question.title }}
                </a>
              </div>
              <div v-else>
                <span>{{ question.title }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="categories.length > 0" class="mt-4">
        <span class="text-libryo-gray-800 font-semibold text-sm">
          {{ t('assess.categories') }}
        </span>
        <div class="mt-0 mb-8">
          <div v-for="category in categories" :key="category.id">
            <span class="text-sm">
              {{ category.title }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
