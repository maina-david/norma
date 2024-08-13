 <x-ui.input :value="old('name', $resource->name ?? '')" name="name" label="{{ __('workflows.rating_group.name') }}" required />

 <x-ui.input :value="old('description', $resource->description ?? '')" name="description" label="{{ __('workflows.rating_group.description') }}" />

 <x-ui.input type="select" :options="$ratingTypes" name="rating_types[]" multiple
             :value="isset($resource) ? $resource->ratingTypes->pluck('id')->toArray() : []" label="{{ __('workflows.rating_type.rating_types') }}" />

 <x-slot name="footer">
   <div></div>
   <div>
     <x-ui.back-button :fallback="route('collaborate.rating-groups.index')" />
     <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
   </div>
 </x-slot>
