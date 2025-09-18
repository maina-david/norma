import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import bus from '@/vue/plugins/bus';
import loading from '@/vue/plugins/loading';
import formats from '@/vue/plugins/formats';
import tooltips from '@/vue/plugins/tooltips';
import i18n from '@/vue/plugins/i18n';

createInertiaApp({
  id: 'my-norma',
  resolve: (name) => {
    const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });

    return pages[`./Pages/${name}.vue`];
  },
  setup({ el, App, props, plugin }) {
    createApp({
      provide() {
        return {
          orgHasModule: this.orgHasModule,
          isOrgAdmin: this.isOrgAdmin,
          isSuperUser: this.isSuperUser,
        };
      },
      methods: {
        orgHasModule(item) {
          return this.$page.props?.stream?.org_modules[item] ?? false;
        },
        isOrgAdmin() {
          return this.$page.props?.auth?.user?.org_ad ?? false;
        },
        isSuperUser() {
          return this.$page.props?.auth?.user?.app_ad ?? false;
        },
      },
      render: () => h(App, props),
    })
      .use(i18n)
      .use(plugin)
      .use(bus)
      .use(loading)
      .use(formats)
      .use(tooltips)
      .mount(el);
  },
});
