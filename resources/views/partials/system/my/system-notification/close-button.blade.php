<x-ui.form method="post"
           :action="route('my.system.system-notifications.dismiss', ['notification' => $systemNotification->id])">
  <x-ui.button type="submit">{{ __('actions.close') }}</x-ui.button>
</x-ui.form>
