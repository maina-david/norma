<div class="my-5 flex justify-end">
  <x-ui.button class="mr-5" data-turbo-frame="_top" type="link" styling="outline" theme="negative"
               :href="route('my.settings.compilation.generic.decompile.show', ['library' => $library->id])">
    {{ __('compilation.generic_compilation.generic_decompile') }}</x-ui.button>

  <x-ui.button data-turbo-frame="_top" type="link" theme="primary"
               :href="route('my.settings.compilation.generic.compile.show', ['library' => $library->id])">
    {{ __('compilation.generic_compilation.generic_compile') }}</x-ui.button>
</div>


<x-corpus.work.work-data-table :base-query="$baseQuery" searchable
                               :fields="['title_with_references', 'status']"
                               :route="route('my.settings.works.for.library.index', ['library' => $library->id])" />
