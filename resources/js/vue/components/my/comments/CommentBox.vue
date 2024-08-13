<script setup>
import { ref } from 'vue';
import AppIcon from '@/vue/components/AppIcon.vue';
import AppButton from '@/vue/components/AppButton.vue';
import { useAxios } from '@/vue/composables/useAxios';

const props = defineProps({
  value: { type: String, default: '' },
  relatedId: { type: [String, Number], default: null },
  relation: { type: String, default: null },
});

const emit = defineEmits(['save']);
const commentBox = ref(null);
const comment = ref(props.value);
const showingEmojis = ref(false);
const loading = ref(false);
const axios = useAxios();

function handlePaste(event) {
  setTimeout(function () {
    var holder = document.createElement('div');
    holder.innerHTML = event.target.innerHTML;
    holder.querySelectorAll('[style]').forEach(function (el) {
      el.setAttribute('style', el.getAttribute('style').replace(/--tw[^;]+;\s*/g, ''));
    });
    event.target.innerHTML = holder.innerHTML;
  }, 100);
}

function handleInput(event) {
  comment.value = event.target.innerHTML;
}

function handleSubmit() {
  loading.value = true;
  axios.post(`/comments/related/${props.relation}/${props.relatedId}`, { comment: comment.value })
    .then(({ data }) => data)
    .then(({ data }) => {
      emit('save', data);
      comment.value = '';
      commentBox.value.innerHTML = '';
    })
    .finally(() => {
      loading.value = false;
    });
}
</script>

<template>
  <div class="mb-4">
    <div class="flex items-start space-x-4">
      <div class="min-w-0 flex-1">
        <form class="relative" @submit.prevent="handleSubmit">
          <div class="border border-libryo-gray-300 rounded-lg shadow-sm overflow-hidden focus-within:border-primary focus-within:ring-1 focus-within:ring-primary">
            <label for="comment" class="sr-only">Add your message</label>

            <div
              ref="commentBox"
              max-length="5000"
              contenteditable="true"
              class="full-width block w-full font-normal px-3 py-4 border-0 resize-none outline-none focus:ring-0 sm:text-sm bg-white"
              @input="handleInput"
              @paste="handlePaste"
              @keydown.enter.ctrl="handleSubmit"
              v-html="value"
            />

            <div v-show="showingEmojis">
              <div class="px-4 mt-6 pb-2 flex text-libryo-gray-500">
                <button type="button" class="-m-2.5 w-10 h-10 rounded-full flex items-center justify-center text-libryo-gray-400 hover:text-libryo-gray-500" @click="showingEmojis = !showingEmojis">
                  <AppIcon name="smile-wink" />
                </button>
              </div>

              <!--              <emoji-picker class="w-full" x-on:emoji-click="handleEmojiClick($event.detail)" />-->
            </div>

            <div class="py-2" aria-hidden="true">
              <div class="py-px">
                <div class="h-9" />
              </div>
            </div>
          </div>

          <div class="absolute bottom-0 inset-x-0 pl-3 pr-2 py-2 flex justify-between">
            <div class="flex items-center space-x-5">
              <div class="flex items-center">
                <button type="button" class="-m-2.5 w-10 h-10 rounded-full flex items-center justify-center text-libryo-gray-400 hover:text-libryo-gray-500" @click="showingEmojis = !showingEmojis">
                  <AppIcon name="smile-wink" />
                </button>
              </div>
            </div>
            <div class="shrink-0">
              <AppButton type="submit" theme="primary">
                {{ $t('comments.post') }}
              </AppButton>
            </div>
          </div>
        </form>

        <!--        @error($name)-->
        <!--        <p class="text-sm text-secondary">-->
        <!--          {{ $message }}-->
        <!--        </p>-->
        <!--        @enderror-->
      </div>
    </div>
  </div>
</template>
