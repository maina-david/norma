<x-ui.form :action="route('my.notify.reminders.store')" method="post">
  @include('partials.notify.reminder.form', [
      'isCreateForm' => false,
      'remindableType' => $remindableType,
      'remindableId' => $remindableId,
  ])

  <x-slot name="footer">
    <div></div>
    <x-ui.button type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
  </x-slot>
</x-ui.form>
