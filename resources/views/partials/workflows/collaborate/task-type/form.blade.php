{{--// max-w-5xl--}}
<div x-data="{ colour: '{{ $resource->colour ?? '#000000' }}' }" class="grid grid-cols-1 md:grid-cols-2 gap-4">
  <x-ui.input name="name"  :value="$resource->name ?? null" :label="__('workflows.task_type.name')" required/>

  <div class="mt-6">
    <x-ui.label for="colour">
      <span class="text-red-400">*</span>
      <span>{{ __('workflows.task_type.colour') }}</span>
    </x-ui.label>

    <div  x-on:click="$refs.colSelector.click()" class="cursor-pointer mt-2 ml-1 text-center text-white px-4 rounded-full inline-flex items-center justify-center" x-bind:style="'background-color:' + colour">
      <span>{{ __('workflows.task_type.colour_selector') }}</span>
    </div>

    <input x-ref="colSelector" name="colour" type="color" x-model="colour" class="h-0 invisible" />
  </div>

  <div>
    <x-ui.input name="course_link"  :value="$resource->course_link ?? null" :label="__('workflows.task_type.course_link')"/>
  </div>

  <x-ui.input type="select" :options="$ratingGroups" name="rating_group_id"  :value="$resource->rating_group_id ?? null" :label="__('workflows.task_type.rating_group')" />

  <x-ui.input type="select" :options="$taskRoutes" name="task_route_id"  :value="$resource->task_route_id ?? null" :label="__('workflows.task_type.task_route_id')" />

  <div></div>

  <div class="mt-2">
    <x-ui.input type="checkbox" name="is_parent"  :value="$resource->is_parent ?? null" :label="__('workflows.task_type.is_parent')"/>
  </div>

  <div class="mt-2">
    <x-ui.input type="checkbox" name="assignee_can_close"  :value="$resource->assignee_can_close ?? null" :label="__('workflows.task_type.assignee_can_close')"/>
  </div>

  <div class="mt-2">
    <x-ui.input type="checkbox" name="has_checks"  :value="$resource->has_checks ?? null" :label="__('workflows.task_type.has_checks')"/>
  </div>


  <x-slot name="footer">
    <div></div>
    <div>
      <x-ui.back-button :fallback="route('collaborate.rating-types.index')" />
      <x-ui.button
          type="submit"
          theme="primary"
      >{{ __('actions.save') }}</x-ui.button>
    </div>
  </x-slot>

</div>
