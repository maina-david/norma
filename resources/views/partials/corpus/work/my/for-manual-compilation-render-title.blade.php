<x-ui.form method="post" data-turbo-frame="_top"
           :action="route('my.settings.compilation.works.for.manual.compilation.add')">

  <input type="hidden" name="work_id" value="{{ $work->id }}" />

  <div class="flex flex-row">
    <div>
      <x-ui.button type="submit" styling="outline" theme="primary" size="xs">
        <x-ui.icon name="plus" />
      </x-ui.button>
    </div>

    <span class="ml-5">{{ $work->title }}</span>
  </div>
  @if ($work->children_count > 0)
    <div class="flex flex-row mt-2">
      <x-ui.input type="checkbox" name="include_children" :value="true"
                  :label="__('compilation.manual_compilation.include_child_works')" />
    </div>
  @endif

</x-ui.form>
