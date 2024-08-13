<div>
  <x-geonames.location.location-selector show-path name="ids[]" required :label="__('compilation.requirements_collection.select_collection')" />

  <div class="mt-5">
    <x-ui.input type="checkbox" :label="__('compilation.requirements_collection.include_parent_collections')" name="include_ancestors" :value="true" />
  </div>
  <div class="mt-5">
    <x-ui.input type="checkbox" :label="__('compilation.requirements_collection.include_descendant_collections')" name="include_descendants" :value="false" />
  </div>

</div>
