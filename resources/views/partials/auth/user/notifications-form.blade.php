<div x-data="{ allDomains: {{ $allLegalDomains ? 'false' : 'true' }} }" class="mb-16">
  <div class="text-lg font-bold">{{ __('auth.user.notifications.updates') }}</div>
  <div class="text-sm text-norma-gray-500 mb-5">{{ __('auth.user.notifications.updates_info') }}</div>

  <x-ui.input @change="allDomains = !allDomains" type="checkbox" :value="$allLegalDomains"
              name="all_legal_domains"
              :label="__('auth.user.notifications.all_categories')" />

  <div class="mt-5" x-show="allDomains">
    @foreach ($domains as $domain)
      <div class="mt-2">
        <x-ui.input type="checkbox"

                    :value="isset($settings['notification_legal_domains']) && in_array($domain->id, $settings['notification_legal_domains']) ? true : false"
                    name="notification_legal_domains[{{ $domain->id }}]"
                    :label="$domain->title" />
      </div>

    @endforeach
  </div>

  <div class="mt-5">
    <x-ui.input type="checkbox" :value="$email_daily" name="email_daily"
                :label="__('auth.user.notifications.daily_email_notifications')" />
    <div class="text-sm text-norma-gray-500 mb-5">{{ __('auth.user.notifications.daily_email_notifications_info') }}</div>

    <x-ui.input type="checkbox" :value="$email_monthly" name="email_monthly"
                :label="__('auth.user.notifications.monthly_email_notifications')" />
    <div class="text-sm text-norma-gray-500 mb-5">{{ __('auth.user.notifications.monthly_email_notifications_info') }}</div>
  </div>
</div>



<div class="mb-16">
  <div class="text-lg font-bold">{{ __('auth.user.notifications.comments') }}</div>
  <div class="text-sm text-norma-gray-500  mb-5">{{ __('auth.user.notifications.comments_info') }}</div>


  <x-ui.input type="checkbox"
              :value="isset($settings['email_comment_mention']) ? $settings['email_comment_mention'] : true"
              name="email_comment_mention"
              :label="__('auth.user.notifications.comment_mention_email')" />
  <div class="text-sm text-norma-gray-500 mb-5">{{ __('auth.user.notifications.comment_mention_email_info') }}</div>

  <x-ui.input type="checkbox" :value="isset($settings['email_comment_reply']) ? $settings['email_comment_reply'] : true"
              name="email_comment_reply"
              :label="__('auth.user.notifications.comment_reply_email')" />
  <div class="text-sm text-norma-gray-500 mb-5">{{ __('auth.user.notifications.comment_reply_email_info') }}</div>

</div>

<div class="mb-16">
  <div class="text-lg font-bold">{{ __('auth.user.notifications.tasks') }}</div>
  <div class="text-sm text-norma-gray-500  mb-5">{{ __('auth.user.notifications.tasks_info') }}</div>


  <x-ui.input type="checkbox" :value="isset($settings['email_task_assigned']) ? $settings['email_task_assigned'] : true"
              name="email_task_assigned"
              :label="__('auth.user.notifications.task_assigned')" />
  <div class="text-sm text-norma-gray-500 mb-5">{{ __('auth.user.notifications.task_assigned_info') }}</div>

  <table>
    <thead>
      <tr>
        <x-ui.th></x-ui.th>
        <x-ui.th>{{ __('auth.user.notifications.assignee') }}</x-ui.th>
        <x-ui.th>{{ __('auth.user.notifications.author') }}</x-ui.th>
        <x-ui.th>{{ __('auth.user.notifications.follower') }}</x-ui.th>
      </tr>
    </thead>
    <tbody>
      @foreach ($taskSettings as $setting)
        <x-ui.tr :loop="$loop">
          <x-ui.td>{{ __('auth.user.notifications.' . $setting['label']) }}</x-ui.td>
          @foreach (['assignee', 'author', 'watcher'] as $person)
            <x-ui.td>
              <div class="grid justify-items-center">
                <x-ui.input type="checkbox"
                            :value="$setting['values'][$person]"
                            :name="$setting['setting'] . '_' . $person"
                            :label="''" />
              </div>
            </x-ui.td>
          @endforeach
        </x-ui.tr>
      @endforeach
    </tbody>
  </table>

</div>

<div class="mb-16">
  <div class="text-lg font-bold">{{ __('auth.user.notifications.reminders') }}</div>
  <div class="text-sm text-norma-gray-500  mb-5">{{ __('auth.user.notifications.reminders_info') }}</div>


  <x-ui.input type="checkbox" :value="isset($settings['email_reminder']) ? $settings['email_reminder'] : true"
              name="email_reminder"
              :label="__('auth.user.notifications.reminder_email')" />
  <div class="text-sm text-norma-gray-500 mb-5">{{ __('auth.user.notifications.reminder_email_info') }}</div>

</div>



<x-slot name="footer">
  <div class="flex flex-row justify-items-end">
    <x-ui.button size="xl" type="submit" theme="primary">{{ __('actions.save') }}</x-ui.button>
  </div>
</x-slot>
