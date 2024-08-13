@php
  use App\Enums\Workflows\TaskTypeOption;
  use App\Enums\Workflows\WorkStatusOption;
  use App\Enums\Workflows\WorkExpressionStatusOption;
  use App\Enums\Workflows\AutoArchiveOption;

  $creating = $creating ?? false;
  $forTask = $forTask ?? false;
  $prefix = $prefix ?? '';
  $excludeFields = $excludeFields ?? [];
@endphp

  <div>
      @if(!in_array("{$prefix}title", $excludeFields))
        <x-ui.input name="{{ $prefix }}title" :value="old($prefix . 'title', $defaults->title ?? null)" :label="__('workflows.task.title')" />
      @endif

      <x-workflows.tasks.task-priority-selector name="{{ $prefix }}priority" :value="old($prefix . 'priority', $defaults->priority ?? null)" />

      @if(!$forTask || auth()->user()->can('collaborate.workflows.task.set-complexity'))
        <x-workflows.tasks.complexity-selector name="{{ $prefix }}complexity" value="{{ old($prefix . 'complexity', $defaults->complexity ?? null) }}" />
      @endif

      @if(!$forTask || auth()->user()->can('collaborate.workflows.task.set-units'))
        <x-ui.input type="number" step="0.01" name="{{ $prefix }}units" value="{{ old($prefix . 'units', $defaults->units ?? null) }}" :label="__('workflows.task.units')" />
      @endif

    @if(!in_array("{$prefix}start_date", $excludeFields))
      <x-ui.datetime-picker
          type="date"
          name="{{ $prefix }}start_date"
          :label="__('workflows.task.start_date')"
          :value="old($prefix . 'start_date', $defaults->start_date ?? null)"
      />

    @endif

    @if(!in_array("{$prefix}due_date", $excludeFields))
      <x-ui.datetime-picker
          name="{{ $prefix }}due_date"
          :label="__('workflows.task.date_due')"
          :value="old($prefix . 'due_date', $defaults->due_date ?? null)"
      />
    @endif

      <div>
        @if(!$creating && !in_array("{$prefix}group_id", $excludeFields))
        <x-collaborators.group-selector name="{{ $prefix }}group_id" :value="old($prefix . 'group_id', $defaults->group_id ?? null)" :label="__('workflows.task.assigned_to_group')" :group="$group ?? null" />
        @endif
        <x-workflows.tasks.task-assignment-selector name="{{ $prefix }}user_id"  :value="old($prefix . 'user_id', $defaults->user_id ?? null)" :label="__('workflows.task.assigned_to')" :user="$users->get($defaults->user_id ?? null)" />

        <x-workflows.tasks.task-assignment-selector name="{{ $prefix }}manager_id"  :value="old($prefix . 'manager_id', $defaults->manager_id ?? null)" :label="__('workflows.task.manager')" :user="$users->get($defaults->manager_id ?? null)" />

      </div>

    @if(!$forTask && !$creating && !in_array("{$prefix}depends_on", $excludeFields))

      <x-ui.input type="select" :options="$previousTypes" name="{{ $prefix }}depends_on" :value="old($prefix . 'depends_on', $defaults->depends_on ?? null)" :label="__('workflows.task.depends_on')" />

      <x-workflows.tasks.dependent-units-selector :value="old($prefix . 'set_dependent_units_type', $defaults->set_dependent_units_type ?? null)" :multiplier="$defaults->set_dependent_units_multiple ?? null" />

      <x-ui.input type="number" name="{{ $prefix }}create_one_for_every" :value="old($prefix . 'create_one_for_every', $defaults->create_one_for_every ?? null)" :label="__('workflows.task.create_one_for_every')" />

    @endif

    @if(!$forTask && !$creating && !in_array("{$prefix}set_current_units", $excludeFields))
    <div class="flex items-center" x-data="{current_units:{{ json_encode((old($prefix . 'set_current_units', $defaults->set_current_units ?? null))) }}}">
        <div class="py-4">
          <x-ui.input type="checkbox" name="{{ $prefix }}set_current_units" x-model="current_units" :label="__('workflows.task.set_current_units')" />
        </div>

        <div x-show="current_units" class="grid grid-cols-2 flex-grow">
          <div class="flex items-center">
            <div class="text-sm font-medium text-libryo-gray-700 block ml-1 mr-2"> {{ __('workflows.task.to') }}</div>
            <div class="flex-grow">
              <x-workflows.tasks.dependent-units-selector
                label=""
                name="set_current_units_type"
                multiple_name="set_current_units_multiple"
                :value="old($prefix . 'set_current_units_type', $defaults->set_current_units_type ?? null)"
                :multiplier="$defaults->set_current_units_multiple ?? null"
              />
            </div>
          </div>

          <div class="flex items-center">
            <div class="text-sm font-medium text-libryo-gray-700 block mx-2"> {{ __('workflows.task.when_task_moves_to') }}</div>
            <div class="flex-grow">
              <x-workflows.tasks.task-status-selector
                name="{{ $prefix }}set_current_units_trigger"
                :value="old($prefix . 'set_current_units_trigger', $defaults->set_current_units_trigger ?? null)"
              />
            </div>
          </div>
        </div>
      </div>


    <div class="flex items-center" x-data="{current_due:{{ json_encode((old($prefix . 'set_current_due_date', $defaults->set_current_due_date ?? null))) }}}">
      <div class="py-4">
        <x-ui.input type="checkbox" name="{{ $prefix }}set_current_due_date" x-model="current_due" :label="__('workflows.task.set_current_due_date')" />
      </div>

      <div x-show="current_due" class="grid grid-cols-2 flex-grow">
        <div class="flex items-center">
          <div class="text-sm font-medium text-libryo-gray-700 block ml-1 mr-2"> {{ __('workflows.task.to') }}</div>
          <div class="flex-grow">
            <x-workflows.tasks.task-auto-due-date-selector name="{{ $prefix }}set_current_task_duration" :value="old($prefix . 'set_current_task_duration', $defaults->set_current_task_duration ?? null)"  />
          </div>
        </div>

        <div class="flex items-center">
          <div class="text-sm font-medium text-libryo-gray-700 block mx-2"> {{ __('workflows.task.when_task_moves_to') }}</div>
          <div class="flex-grow">
            <x-workflows.tasks.task-status-selector name="{{ $prefix }}set_current_due_date_trigger" :value="old($prefix . 'set_current_due_date_trigger', $defaults->set_current_due_date_trigger ?? null)"  />
          </div>
        </div>
      </div>
    </div>

    <div class="flex items-center" x-data="{due:{{ json_encode((old($prefix . 'set_dependent_due_date', $defaults->set_dependent_due_date ?? null))) }}}">
      <div class="py-4">
        <x-ui.input type="checkbox" name="{{ $prefix }}set_dependent_due_date" x-model="due" :label="__('workflows.task.set_dependent_due_date')" />
      </div>

      <div x-show="due" class="grid grid-cols-2 flex-grow">
        <div class="flex items-center">
          <div class="text-sm font-medium text-libryo-gray-700 block ml-1 mr-2"> {{ __('workflows.task.to') }}</div>
          <div class="flex-grow">
            <x-workflows.tasks.task-auto-due-date-selector name="{{ $prefix }}set_dependent_task_duration" :value="old($prefix . 'set_dependent_task_duration', $defaults->set_dependent_task_duration ?? null)"  />
          </div>
        </div>

        <div class="flex items-center">
          <div class="text-sm font-medium text-libryo-gray-700 block mx-2"> {{ __('workflows.task.when_task_moves_to') }}</div>
          <div class="flex-grow">
            <x-workflows.tasks.task-status-selector name="{{ $prefix }}set_dependent_due_date_trigger" :value="old($prefix . 'set_dependent_due_date_trigger', $defaults->set_dependent_due_date_trigger ?? null)"  />
          </div>
        </div>
      </div>
    </div>


    <div class="flex items-center" x-data="{move:{{ json_encode((old($prefix . 'move_dependent_task', $defaults->move_dependent_task ?? null))) }}}">
      <div class="py-4">
        <x-ui.input type="checkbox" name="{{ $prefix }}move_dependent_task" x-model="move" :label="__('workflows.task.move_dependent_task')" />
      </div>

      <div x-show="move" class="grid grid-cols-2 flex-grow">
        <div class="flex items-center">
          <div class="text-sm font-medium text-libryo-gray-700 block ml-1 mr-2"> {{ __('workflows.task.to') }}</div>
          <div class="flex-grow">
            <x-workflows.tasks.task-status-selector name="{{ $prefix }}move_dependent_task_status" :value="old($prefix . 'move_dependent_task_status', $defaults->move_dependent_task_status ?? null)"  />
          </div>
        </div>

        <div class="flex items-center">
          <div class="text-sm font-medium text-libryo-gray-700 block mx-2"> {{ __('workflows.task.when_task_moves_to') }}</div>
          <div class="flex-grow">
            <x-workflows.tasks.task-status-selector name="{{ $prefix }}move_dependent_task_trigger" :value="old($prefix . 'move_dependent_task_trigger', $defaults->move_dependent_task_trigger ?? null)"  />
          </div>
        </div>
      </div>
    </div>

    <div class="flex items-center" x-data="{cache:{{ json_encode((old($prefix . 'cache_related_work', $defaults->cache_related_work ?? null))) }}}">
      <div class="py-4">
        <x-ui.input type="checkbox" name="{{ $prefix }}cache_related_work" x-model="cache" :label="__('workflows.task.cache_related_work')" />
      </div>

      <div x-show="cache" class="flex items-center flex-grow">
        <div class="text-sm font-medium text-libryo-gray-700 block ml-1 mr-2"> {{ __('workflows.task.when_task_moves_to') }}</div>
        <div class="flex-grow">
          <x-workflows.tasks.task-status-selector name="{{ $prefix }}cache_related_work_trigger" :value="old($prefix . 'cache_related_work_trigger', $defaults->cache_related_work_trigger ?? null)"  />
        </div>
      </div>
    </div>

    <div class="flex items-center" x-data="{publish:{{ json_encode((old($prefix . 'publish_related_work', $defaults->publish_related_work ?? null))) }}}">
      <div class="py-4">
        <x-ui.input type="checkbox" name="{{ $prefix }}publish_related_work" x-model="publish" :label="__('workflows.task.publish_related_work')" />
      </div>

      <div x-show="publish" class="flex items-center flex-grow">
        <div class="text-sm font-medium text-libryo-gray-700 block ml-1 mr-2"> {{ __('workflows.task.when_task_moves_to') }}</div>
        <div class="flex-grow">
          <x-workflows.tasks.task-status-selector name="{{ $prefix }}publish_related_work_trigger" :value="old($prefix . 'publish_related_work_trigger', $defaults->publish_related_work_trigger ?? null)"  />
        </div>
      </div>
    </div>

    <div class="flex items-center" x-data="{generate:{{ json_encode((old($prefix . 'generate_reference_extracts', $defaults->generate_reference_extracts ?? null))) }}}">
      <div class="py-4">
        <x-ui.input type="checkbox" name="{{ $prefix }}generate_reference_extracts" x-model="generate" :label="__('workflows.task.generate_reference_extracts')" />
      </div>

      <div x-show="generate" class="flex items-center flex-grow">
        <div class="text-sm font-medium text-libryo-gray-700 block ml-1 mr-2"> {{ __('workflows.task.when_task_moves_to') }}</div>
        <div class="flex-grow">
          <x-workflows.tasks.task-status-selector name="{{ $prefix }}generate_reference_extracts_trigger" :value="old($prefix . 'generate_reference_extracts_trigger', $defaults->generate_reference_extracts_trigger ?? null)"  />
        </div>
      </div>
    </div>

    <div class="py-4">
      <x-ui.input type="checkbox" name="{{ $prefix }}set_dependent_complexity" :value="old($prefix . 'set_dependent_complexity', $defaults->set_dependent_complexity ?? null)" :label="__('workflows.task.set_dependent_complexity')" />
    </div>
    @endif

    <x-ui.textarea wysiwyg="basic" name="{{ $prefix }}description" :value="old($prefix . 'description', $defaults->description ?? null)" :label="__('workflows.task.description')" />

  @if(!$creating && !in_array("{$prefix}auto_payment", $excludeFields))
      <div class="py-4">
        <x-ui.input type="checkbox" name="{{ $prefix }}auto_payment" :value="old($prefix . 'auto_payment', $defaults->auto_payment ?? null)" :label="__('workflows.task.auto_payment')" />
      </div>

      <div class="py-4">
        <x-ui.input type="checkbox" name="{{ $prefix }}requires_rating" :value="old($prefix . 'requires_rating', $defaults->requires_rating ?? null)" :label="__('workflows.task.requires_rating')" />
      </div>
    @endif

    @if($creating)
        <x-ui.file-selector name="{{ $prefix }}attachment" />
    @endif

  </div>

@if($withValidation ?? false)
  <div class="grid grid-cols-2 gap-4">
    <div class="md:col-span-2 font-semibold border-b border-libryo-gray-200 mt-6">
      {{ __('workflows.task_type.validations') }}
    </div>

    <x-ui.input type="select" :options="$taskTypeOptions" name="{{ $prefix }}validations[status][legal_domains]"  :value="old($prefix. 'validations[status][legal_domains]', $defaults->validations['status']['legal_domains'] ?? TaskTypeOption::NONE->value)" :label="__('workflows.task_type.legal_domains')" />
    <x-ui.input type="select" :options="$taskTypeOptions" name="{{ $prefix }}validations[status][locations]"  :value="old($prefix. 'validations[status][locations]', $defaults->validations['status']['locations'] ?? TaskTypeOption::NONE->value)" :label="__('workflows.task_type.locations')" />
    <x-ui.input type="select" :options="$taskTypeOptions" name="{{ $prefix }}validations[status][statements]"  :value="old($prefix. 'validations[status][statements]', $defaults->validations['status']['statements'] ?? TaskTypeOption::NONE->value)" :label="__('workflows.task_type.legal_statements')" />
    <x-ui.input type="select" :options="$taskTypeWithoutCompleted" name="{{ $prefix }}validations[status][identified_citations]"  :value="old($prefix. 'validations[status][identified_citations]', $defaults->validations['status']['identified_citations'] ?? TaskTypeOption::NONE->value)" :label="__('workflows.task_type.identified_citations')" />
    <x-ui.input type="select" :options="$taskTypeWithoutCompleted" name="{{ $prefix }}validations[status][citations]"  :value="old($prefix. 'validations[status][citations]', $defaults->validations['status']['citations'] ?? TaskTypeOption::NONE->value)" :label="__('workflows.task_type.citations')" />
    <x-ui.input type="select" :options="$taskTypeWithoutCompleted" name="{{ $prefix }}validations[status][metadata]"  :value="old($prefix. 'validations[status][metadata]', $defaults->validations['status']['metadata'] ?? TaskTypeOption::NONE->value)" :label="__('workflows.task_type.metadata')" />
    <x-ui.input type="select" :options="$taskTypeOptions" name="{{ $prefix }}validations[status][summaries]"  :value="old($prefix. 'validations[status][summaries]', $defaults->validations['status']['summaries'] ?? TaskTypeOption::NONE->value)" :label="__('workflows.task_type.summaries')" />
    <x-ui.input type="select" :options="$taskTypeOptions" name="{{ $prefix }}validations[status][work_highlights]"  :value="old($prefix. 'validations[status][work_highlights]', $defaults->validations['status']['work_highlights'] ?? TaskTypeOption::NONE->value)" :label="__('workflows.task_type.work_highlights')" />
    <x-ui.input type="select" :options="$taskTypeOptions" name="{{ $prefix }}validations[status][work_update_report]"  :value="old($prefix. 'validations[status][work_update_report]', $defaults->validations['status']['work_update_report'] ?? TaskTypeOption::NONE->value)" :label="__('workflows.task_type.work_update_report')" />
    <x-ui.input type="select" :options="WorkStatusOption::forSelector()" name="{{ $prefix }}validations[status][work]" :value="old($prefix. 'validations[status][work]', $defaults->validations['status']['work'] ?? WorkStatusOption::NONE->value)" :label="__('workflows.task_type.work_status')" />
    <x-ui.input type="select" :options="AutoArchiveOption::forSelector()" name="{{ $prefix }}validations[on_todo][auto_archive]" :value="old($prefix. 'validations[on_todo][auto_archive]', $defaults->validations['on_todo']['auto_archive'] ?? AutoArchiveOption::NONE->value)" :label="__('workflows.task_type.auto_archive')" />
    <x-ui.input type="select" :options="WorkExpressionStatusOption::forSelector()" name="{{ $prefix }}validations[status][work_expression]" :value="old($prefix. 'validations[status][work_expression]', $defaults->validations['status']['work_expression'] ?? WorkExpressionStatusOption::NONE->value)" :label="__('workflows.task_type.work_expression_status')" />

    <div class="mt-2">
      <x-ui.input checkbox-value="{{ TaskTypeOption::APPLIED->value}}" value="{{ TaskTypeOption::APPLIED === TaskTypeOption::tryFrom($defaults->validations['status']['complexity'] ?? -1)}}" type="checkbox" name="{{ $prefix }}validations[status][complexity]"  :label="__('workflows.task_type.complexity_set')"/>
    </div>
  </div>
@endif

@if(!$creating)
  <x-slot name="footer">
    <div></div>
    <div>
      <x-ui.back-button :fallback="route('collaborate.tasks.index')" />
      <x-ui.button
          type="submit"
          theme="primary"
      >{{ __('actions.save') }}</x-ui.button>
    </div>
  </x-slot>
@endif
