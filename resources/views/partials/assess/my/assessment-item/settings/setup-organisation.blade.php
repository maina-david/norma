<div class="mt-10">
  @if ($count > 0)
    <x-ui.alert-box>
      {{ trans_choice('assess.setup.count_unused_items', $count, ['value' => $count]) }}
    </x-ui.alert-box>

    <div class="mt-10">
      <x-ui.form method="post"
                 :action="route('my.settings.assess.setup.for.organisation.activate.items', ['organisation' => $organisation])"
                 data-turbo-frame="_top">
        <x-ui.button size="xl" type="submit" theme="primary">{{ __('assess.setup.activate_unused_items') }}
        </x-ui.button>
      </x-ui.form>
    </div>
  @else
    <x-ui.empty-state-icon icon="check-circle"
                           :title="__('assess.setup.no_unused_items_in_org')" />
  @endif
</div>
