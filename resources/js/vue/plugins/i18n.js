import { createI18n } from 'vue-i18n';
import messages from '@/lang';

const i18n = createI18n({
  legacy: false,
  locale: navigator.language.split('-')[0],
  fallbackLocale: 'en',
  messages,
});

export default i18n;
