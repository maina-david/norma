<script setup>
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import UserAvatar from '@/vue/components/my/users/UserAvatar.vue';
import AppIcon from '@/vue/components/AppIcon.vue';
import DropDown from '@/vue/components/DropDown.vue';
import UploadedFiles from '@/vue/components/my/files/UploadedFiles.vue';
import CommentListing from '@/vue/components/my/comments/CommentListing.vue';
import DeleteButton from '@/vue/components/DeleteButton.vue';

const page = usePage();
const emit = defineEmits(['delete']);
const props = defineProps({
  comment: { type: Object, required: true },
  relatedId: { type: [String, Number], default: null },
  relation: { type: String, default: null },
  reply: { type: Boolean, default: false },
});

const isAuthor = computed(() => props.comment.author_id === page.props.auth.user.id);

const showingReplies = ref(false);
const showingFiles = ref(false);

function deleteComment(toggle) {
  toggle();
  emit('delete');
}

function updateCounts() {
  props.comment.comments_count = (props.comment.comments_count ?? 0) + 1;
}
</script>

<template>
  <div>
    <div class="hover:bg-libryo-gray-50 py-2">
      <div class="flex flex-row justify-between mb-2">
        <div class="mr-3 flex-shrink-0">
          <UserAvatar :user="comment.author" :dimensions="12" />
        </div>
        <div class="w-full">
          <div class="flex justify-between">
            <div class="flex-grow col-span-2 text-sm font-medium mb-2">
              {{ comment.author.name }}
            </div>
            <div class="col-span-1 flex items-center">
              <button v-if="!reply" class="mr-5" @click="showingReplies = true">
                <AppIcon name="reply" class="cursor-pointer tippy" :data-tippy-content="$t('comments.reply_to_comment')" />
              </button>
              <div v-if="isAuthor" class="mr-5" @click="showingFiles = true">
                <AppIcon name="paperclip" class="cursor-pointer tippy" :data-tippy-content="$t('storage.attach_files')" />
              </div>
              <div class="pr-2">
                <DropDown v-if="isAuthor" position="left">
                  <template #trigger="{ toggle }">
                    <button @click.stop="toggle">
                      <AppIcon name="ellipsis-v" class="cursor-pointer" />
                    </button>
                  </template>

                  <template #default="{ toggle }">
                    <div>
                      <DeleteButton
                        :target="`/comments/${comment.id}`"
                        no-border
                        @delete="() => deleteComment(toggle)"
                      >
                        <AppIcon name="trash-alt" class="mr-5" />
                        {{ $t('comments.delete_comment') }}
                      </DeleteButton>
                    </div>
                  </template>
                </DropDown>
              </div>
            </div>
          </div>
          <div>
            <div class="text-sm" v-html="$format.links(comment.comment)" />

            <div v-if="!comment.place_id" class="text-xs text-libryo-gray-400 italic mt-2">
              {{ $t('comments.added_to_all_streams') }}
            </div>
          </div>
        </div>
      </div>

      <div class="grid justify-items-end">
        <div class="italic text-libryo-gray-400 text-xs">
          {{ $format.datetime(comment.created_at) }}
        </div>
      </div>
    </div>

    <div
      v-if="comment.files_count && comment.files_count > 0"
      class="py-1 bg-libryo-gray-50 text-center hover:bg-libryo-gray-100 cursor-pointer text-primary"
      @click="showingFiles = !showingFiles"
    >
      {{ $t('comments.comment_files_count', comment.files_count, { value: comment.files_count }) }}
    </div>

    <div v-if="showingFiles" class="ml-12 p-3 bg-libryo-gray-50 mt-4">
      <KeepAlive>
        <UploadedFiles
          requires-folder
          :can-upload="isAuthor"
          :libryo-id="comment.place_id"
          :related-id="comment.id"
          relation="comment"
          multiple
        />
      </KeepAlive>
    </div>

    <div
      v-if="!reply && comment.comments_count > 0"
      class="mt-1 py-1 bg-libryo-gray-50 text-center hover:bg-libryo-gray-100 cursor-pointer text-primary"
      @click="showingReplies = !showingReplies"
    >
      {{ $t('comments.comment_replies_count', comment.comments_count, { 'value': comment.comments_count}) }}
    </div>

    <div v-if="!reply && showingReplies" class="ml-12 p-3 bg-libryo-gray-50 mt-4">
      <KeepAlive>
        <CommentListing
          reply
          :related-id="comment.id"
          relation="comment"
          @save="updateCounts"
          @delete="() => emit('delete')"
        />
      </KeepAlive>
    </div>
  </div>
</template>
