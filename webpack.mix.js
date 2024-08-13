const mix = require("laravel-mix");
//const tailwindcss = require("tailwindcss");
require("laravel-mix-purgecss");

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.disableSuccessNotifications();

mix.js("resources/js/app.js", "public/js")
    .js("resources/js/collaborate/annotations.js", "public/js")
    .js("resources/js/collaborate/catalogue-expression.js", "public/js")
    .js("resources/js/collaborate/catalogue-test.js", "public/js")
    .js("resources/js/collaborate/document-editor.js", "public/js")
    .js("resources/js/collaborate/toc.js", "public/js").vue()
    .postCss('resources/css/app.css', 'public/css')
    .options({
      postCss: [require('tailwindcss')('./tailwind.config.js')]
    })
    .extract();
//.postCss("resources/css/app.css", "public/css", [
//    require("postcss-import"),
//    require("tailwindcss"),
//    require("autoprefixer"),
//]);

if (mix.inProduction()) {
  mix.version();
  mix.then(() => {
    const convertToFileHash = require("laravel-mix-make-file-hash");
    convertToFileHash({
      publicPath: "public",
      manifestFilePath: "public/mix-manifest.json"
    });
  });

}
