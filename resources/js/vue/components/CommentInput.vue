<script setup>
import { nextTick, ref } from 'vue';
import NormaIcon from '@/collaborate/components/NormaIcon.vue';
import WysiwygEditor from '@/vue/components/WysiwygEditor.vue';

const props = defineProps({
  modelValue: { type: String, default: '' },
});

const emit = defineEmits(['modelValue:update']);

const initialContent = ref(props.modelValue);
const content = ref(props.modelValue);
const editor = ref(true);

function handleSubmission() {
  if (content.value.length < 3) {
    return;
  }

  initialContent.value = content.value;
  emit('modelValue:update', content.value);
  editor.value = false;
  nextTick(() => {
    content.value = '';
    initialContent.value = '';
    editor.value = true;
  });
}

</script>

<template>
  <div class="relative notranslate">
    <div class="relative rounded-lg overflow-hidden">
      <label for="comment_box" class="sr-only">Add your message</label>

      <WysiwygEditor
        v-if="editor"
        v-model="content"
        placeholder="Add your message..."
        type="minimal"
        rows="3"
        :max-height="200"
      />

      <!--      <div-->
      <!--        ref="editor"-->
      <!--        contenteditable="true"-->
      <!--        class="full-width block w-full font-normal px-3 py-4 border-0 resize-none outline-none focus:ring-0 sm:text-sm bg-white"-->
      <!--        max-length="5000"-->
      <!--        @input="updateContent"-->
      <!--        @paste="handlePaste"-->
      <!--        v-html="initialContent"-->
      <!--      />-->

      <div class="absolute bottom-0.5 right-0.5 z-10 bg-white rounded-lg">
        <div class="pl-3 pr-2 py-2 flex justify-end">
          <div class="shrink-0">
            <button type="button" class="hover:text-primary text-norma-gray-800 border border-gray-800 hover:border-primary rounded-full h-10 w-10 flex items-center justify-center" @click="handleSubmission">
              <NormaIcon name="paper-plane" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
</style>
