<script setup>
import { onUnmounted, ref } from 'vue';
import AppButton from '@/vue/components/AppButton.vue';
import AppIcon from '@/vue/components/AppIcon.vue';
import { useAxios } from '@/vue/composables/useAxios';

const props = defineProps({
  endpoint: { type: String, required: true },
  icon: { type: String, default: 'download' },
  getAppliedFilters: { type: Function, required: true },
  applyFilters: { type: Function, required: true },
  hasBody: { type: Boolean, default: false },
});

const percentage = ref(0);
const open = ref(false);
const bodyOpen = ref(false);
const bodyParams = ref({});
const axios = useAxios();
const job = ref(null);
const poll = ref(null);

function resetDownload() {
  percentage.value = 0;
  open.value = false;
  job.value = null;
  if (poll.value) {
    clearTimeout(poll.value);
  }
  poll.value = null;
}

function pollJob() {
  axios.post(`/job-statuses/${job.value.job}`)
    .then(({ data }) => data)
    .then(({ data }) => {
      percentage.value = data.percentage;

      if (percentage.value !== 100) {
        poll.value = setTimeout(pollJob, 1000);
        return;
      }

      window.location.href = job.value.target;
      resetDownload();
    });
}

function triggerJob() {
  axios.get(props.endpoint, { params: { ...bodyParams.value, ...props.getAppliedFilters() } })
    .then(({ data }) => data)
    .then(({ data }) => {
      job.value = data;
      pollJob();
    });
}

function handleExport() {
  bodyOpen.value = false;
  open.value = true;
  props.applyFilters(triggerJob);
}

function handleTrigger() {
  if (!props.hasBody) {
    handleExport();
    return;
  }

  bodyOpen.value = true;
}

function handleClose() {
  bodyOpen.value = false;
  open.value = false;
  resetDownload();
}

function updateBodyPayload(payload) {
  bodyParams.value = { ...payload };
}

onUnmounted(() => {
  resetDownload();
});
</script>

<template>
  <div>
    <AppButton v-tooltip="$t('interface.export')" @click="handleTrigger">
      <slot name="trigger">
        <span>Export</span>
      </slot>
    </AppButton>

    <transition
      enter-active-class="ease-out duration-300"
      enter-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
      enter-to-class="opacity-100 translate-y-0 sm:scale-100"
      leave-active-class="ease-in duration-200"
      leave-class="opacity-100 translate-y-0 sm:scale-100"
      leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
      <div v-if="hasBody && bodyOpen" class="z-50 fixed inset-0 h-screen w-screen bg-gray-500 bg-opacity-75 flex items-start justify-center pt-10">
        <div class="inline-block align-bottom bg-white rounded-lg shadow-xl transform transition-all relative border border-norma-gray-200 max-w-screen-90">
          <slot
            :update-body-payload="updateBodyPayload"
            :trigger-export="handleExport"
            :cancel-export="handleClose"
          />
        </div>
      </div>
    </transition>

    <transition
      enter-active-class="ease-out duration-300"
      enter-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
      enter-to-class="opacity-100 translate-y-0 sm:scale-100"
      leave-active-class="ease-in duration-200"
      leave-class="opacity-100 translate-y-0 sm:scale-100"
      leave-to-class="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
      <div v-if="open" class="z-50 fixed inset-0 h-screen w-screen bg-gray-500 bg-opacity-75 flex items-start justify-center pt-10">
        <div class="inline-block align-bottom bg-white rounded-lg shadow-xl transform transition-all relative border border-norma-gray-200 p-4 w-screen-50">
          <div class="flex justify-end">
            <button @click="handleClose">
              <AppIcon name="times" />
            </button>
          </div>

          <div class="p-10">
            <div class="flex flex-col justify-items-center items-center">
              <AppIcon name="cogs" size="40" type="duotone" class="text-primary opacity-80" />

              <div class="mt-8 flex flex-col justify-items-center items-center">
                <span class="font-bold text-lg text-center">{{ $t('actions.generating_export') }}...</span>
              </div>
            </div>

            <div class="relative pt-8">
              <div class="overflow-hidden h-2 text-xs flex rounded bg-norma-gray-200">
                <div
                  :style="`width: ${percentage}%;`"
                  class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-primary"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>
