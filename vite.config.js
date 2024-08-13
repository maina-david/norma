import path from 'node:path';
import fs from 'node:fs';
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

const host = `${path.basename(__dirname)}.test`;

const certs = fs.existsSync(`${process.env.HOME}/.config/valet`)
  ? `${process.env.HOME}/.config/valet/Certificates/${host}`
  : `${process.env.HOME}/.valet/Certificates/${host}`;

let server = {};

if (fs.existsSync(`${certs}.key`)) {
  server = {
    host,
    hmr: { host },
    https: {
      key: fs.readFileSync(`${certs}.key`),
      cert: fs.readFileSync(`${certs}.crt`),
    },
  };
}

export default defineConfig({
  server,
  plugins: [
    laravel({
      input: [
        'resources/css/app.css',
        'resources/js/app.js',
        'resources/js/inertia.js',
        'resources/js/vue/sharable-components.js',
        'resources/js/vue/pages/annotations/index.js',
        'resources/js/vue/pages/identify/index.js',
        'resources/js/collaborate/annotations.js',
        'resources/js/collaborate/catalogue-expression.js',
        'resources/js/collaborate/catalogue-test.js',
        'resources/js/collaborate/document-editor.js',
        'resources/js/collaborate/toc.js',
      ],
    }),
    vue({
      template: {
        compilerOptions: {
          isCustomElement: (tag) => ['turbo-frame'].includes(tag),
        },
        transformAssetUrls: {
          base: null,
          includeAbsolute: false,
        },
      },
    }),
  ],
  resolve: {
    alias: {
      vue: 'vue/dist/vue.esm-bundler.js',
    },
  },
});
