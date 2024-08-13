<script setup>
const props = defineProps({
  dimensions: { type: [String, Number], default: 8 },
  user: { type: Object, required: true },
});

const avatarSuffix = props.user.avatar.includes('ui-avatars.com') ? '' : (props.dimensions > 20 ? '/medium' : '/small');
</script>

<template>
  <div class="ring-white ring-1 rounded-full tippy" :class="`h-${dimensions } w-${dimensions}`">
    <img
      v-tooltip="user.name"
      :class="`h-${dimensions } w-${dimensions}`"
      class="rounded-full tippy object-cover object-center"
      :data-tippy-content="user.name"
      data-tippy-delay="300"
      data-tippy-animation="scale-subtle"
      :src="`${user.avatar}${avatarSuffix}`"
      :alt="user.name"
    >
  </div>
</template>
