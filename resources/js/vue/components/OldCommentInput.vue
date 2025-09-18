<script setup>
import { nextTick, onMounted, ref } from 'vue';
import { Picker } from 'emoji-picker-element';
import NormaIcon from '@/collaborate/components/NormaIcon.vue';

const props = defineProps({
  modelValue: { type: String, default: '' },
});
const emit = defineEmits(['modelValue:update']);

const emojiPickerContainer = ref(null);

onMounted(() => {
  const emojiPicker = new Picker();
  emojiPicker.addEventListener('emoji-click', (event) => {
    editor.value.focus();
    editor.value.innerHTML = editor.value.innerHTML + event.detail.unicode;
    content.value = editor.value.innerHTML;
    window.placeCaretAtEnd(editor.value);
  });

  emojiPickerContainer.value.appendChild(emojiPicker);
});
const initialContent = ref(props.modelValue);
const content = ref(props.modelValue);
const showingEmojis = ref(false);
const editor = ref(false);

function updateContent(event) {
  content.value = event.target.innerHTML;
}

function handlePaste(event) {
  setTimeout(() => {
    const holder = document.createElement('div');
    holder.innerHTML = event.target.innerHTML;
    holder.querySelectorAll('[style]').forEach((el) => el.removeAttribute('style'));
    holder.querySelectorAll('[style]').forEach((el) => el.removeAttribute('style'));
    event.target.innerHTML = holder.innerHTML;
  }, 500);
}

function handleSubmission() {
  initialContent.value = content.value;
  emit('modelValue:update', content.value);
  nextTick(() => {
    content.value = '';
    initialContent.value = '';
  });
}

</script>

<template>
  <div class="relative notranslate">
    <div class="border border-gray-300 rounded-lg shadow-sm overflow-hidden focus-within:border-primary focus-within:ring-1 focus-within:ring-primary">
      <label for="comment_box" class="sr-only">Add your message</label>

      <div
        ref="editor"
        contenteditable="true"
        class="full-width block w-full font-normal px-3 py-4 border-0 resize-none outline-none focus:ring-0 sm:text-sm bg-white"
        max-length="5000"
        @input="updateContent"
        @paste="handlePaste"
        v-html="initialContent"
      />

      <div>
        <div class="pl-3 pr-2 py-2 flex justify-between">
          <div class="flex items-center space-x-5">
            <div class="flex items-center">
              <button type="button" class="-m-1.5 w-10 h-10 rounded-full flex items-center justify-center text-norma-gray-400 hover:text-norma-gray-500" @click="showingEmojis = !showingEmojis">
                <NormaIcon name="smile-wink" />
              </button>
            </div>
          </div>
          <div class="shrink-0 px-4">
            <button type="button" class="text-primary" @click="handleSubmission">
              <NormaIcon name="paper-plane" />
            </button>
          </div>
        </div>

        <div v-show="showingEmojis" ref="emojiPickerContainer" class="w-full" />
      </div>
    </div>
  </div>
</template>

<style>
emoji-picker {
  @apply w-full;
}
</style>
