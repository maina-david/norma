<x-layouts.app>
  <x-slot name="header">
    {{ __('my.hello', ['name' => $user->fname]) }}
  </x-slot>

  <x-slot name="actions">
    <div class="-mt-3">
      <a target="_blank"
         href="https://success.norma.com/en/knowledge/getting-started-with-norma/dashboard/understanding-your-dashboard">
        <x-ui.icon name="question-circle" />
      </a>
    </div>
  </x-slot>

  @if($pendingApplicability > 0 && auth()->user()->canManageApplicability())
    <livewire:compilation.context-question.unanswered-applicability-modal lazy :pending-count="$pendingApplicability" />
  @endif

  <div class="grid grid-cols-12 md:gap-8">
    <div class="col-span-12 lg:col-span-7">
      <x-auth.user.my.stats :user="$user" />

      <div class="my-8">
        <x-notify.legal-update.unread-updates :user="$user" />

      </div>
    </div>
    <aside class="col-span-12 lg:col-span-5 block">
      <div>
        <x-ui.collapse show icon="comment-exclamation" :title="__('notify.notification.alerts')">
          <div class="-m-4">
            <x-ui.tabs>
              <x-slot name="nav">
                <x-ui.tab-nav name="all">{{ __('notify.notification.all') }}</x-ui.tab-nav>
                <x-ui.tab-nav name="unread" class="relative">
                  {{ __('notify.notification.unread') }}
                  @if ($unreadNotificationsCount > 0)
                    <x-ui.badge small class="absolute top-1 -right-2.5">{{ $unreadNotificationsCount }}
                    </x-ui.badge>
                  @endif
                </x-ui.tab-nav>
              </x-slot>

              <div class="py-5">
                <x-ui.tab-content name="all">
                  <turbo-frame loading="lazy"
                               src="{{ route('my.notify.notifications.index', ['readUnread' => 'read']) }}"
                               id="notify-notifications-for-read-{{ $user->id }}"></turbo-frame>
                </x-ui.tab-content>
                <x-ui.tab-content name="unread">
                  <turbo-frame loading="lazy"
                               src="{{ route('my.notify.notifications.index', ['readUnread' => 'unread']) }}"
                               id="notify-notifications-for-unread-{{ $user->id }}"></turbo-frame>
                </x-ui.tab-content>
              </div>

            </x-ui.tabs>
          </div>
        </x-ui.collapse>
      </div>
    </aside>
  </div>


</x-layouts.app>
