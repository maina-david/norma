<script setup>
import SelectElement from '@/vue/components/SelectElement.vue';

const value = defineModel();

const languages = [
  { label: 'Original', value: ' ' },
  { label: 'Afrikaans', value: 'af' },
  { label: 'Chinese (Simplified)', value: 'zh' },
  { label: 'Danish', value: 'da' },
  { label: 'Dutch', value: 'nl' },
  { label: 'English', value: 'en' },
  { label: 'French', value: 'fr' },
  { label: 'German', value: 'de' },
  { label: 'Hindi', value: 'hi' },
  { label: 'Italian', value: 'it' },
  { label: 'Japanese', value: 'ja' },
  { label: 'Korean', value: 'ko' },
  { label: 'Portuguese', value: 'pt' },
  { label: 'Russian', value: 'ru' },
  { label: 'Spanish', value: 'es' },
  { label: 'Swahili', value: 'sw' },
  { label: 'Swedish', value: 'sv' },
  { label: 'Xhosa', value: 'xh' },
  { label: 'Zulu', value: 'zu' },
  { label: 'Polish', value: 'po' },
];
</script>

<template>
  <SelectElement v-model="value" :placeholder="$t('requirements.summary.translate_to')" :options="languages" />
</template>
