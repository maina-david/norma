@php use App\Enums\Notify\LegalUpdatePublishedStatus; @endphp
<x-layouts.app>
  @if($update->status == LegalUpdatePublishedStatus::NOT_APPLICABLE->value)
    <div class="h-screen-75 w-full flex items-center justify-center text-lg">
      <p class="px-4 text-center">
        {!! __('notify.legal_update.not_applicable_info') !!}
      </p>
    </div>

  @else

    <x-slot name="header">
      <div class="flex items-center w-full">
        <div class="mr-6 shrink-0">
          <x-ui.back-referrer-button fallback="{{ route('my.notify.legal-updates.index') }}" />
        </div>
        <div class="flex-grow">
          {{ $update->title }}
        </div>
      </div>
    </x-slot>

    <x-slot:actions>
      @include('bookmarks.bookmark-button', ['bookmarks' => $update->bookmarks, 'turboKey' => "legal-update-{$update->id}", 'routePrefix' => 'my.legal-update.bookmarks', 'routePayload' => ['update' => $update->id]])
    </x-slot:actions>

    <div class="shadow p-3">
      <x-ui.card class="mb-10">

        <dl class="lg:divide-y lg:divide-libryo-gray-200">
          <div class="py-4 lg:py-5 lg:grid lg:grid-cols-5 lg:gap-4 lg:px-6">
            <dt class="text-sm font-medium text-libryo-gray-500">
              {{ __('notify.legal_update.notified_about') }}
            </dt>
            <dd class="mt-1 text-sm text-libryo-gray-900 lg:mt-0 lg:col-span-4">
              <a href="{{ route('my.notify.legal-updates.preview', ['update' => $update->id]) }}"
                 class="text-primary">{{ __('notify.legal_update.view_document') }}</a>
            </dd>
          </div>
          <div class="py-4 lg:py-5 lg:grid lg:grid-cols-5 lg:gap-4 lg:px-6">
            <dt class="text-sm font-medium text-libryo-gray-500">
              {{ __('notify.legal_update.date_notified') }}
            </dt>
            <dd class="mt-1 text-sm text-libryo-gray-900 lg:mt-0 lg:col-span-4">
              <x-ui.timestamp :timestamp="$update->notification_date"/>
            </dd>
          </div>
          @if ($update->primaryLocation)
            <div class="py-4 lg:py-5 lg:grid lg:grid-cols-5 lg:gap-4 lg:px-6">
              <dt class="text-sm font-medium text-libryo-gray-500">
                {{ __('notify.legal_update.jurisdiction') }}
              </dt>
              <dd class="mt-1 text-sm text-libryo-gray-900 lg:mt-0 lg:col-span-4">

                <x-ui.country-flag class="rounded-full inline-block w-6 h-6 mr-4"
                                   :country-code="$update->primaryLocation->flag"/>
                <span>
                @foreach($update->primaryLocation->ancestorsWithSelf as $jur)
                    {{ $jur->title }}
                  @if(!$loop->last) > @endif
                @endforeach
              </span>
              </dd>
            </div>
          @endif

        </dl>

        @if ($organisation->translation_enabled)
          <div class="grid justify-items-end" x-data="{}">
            <div class="w-52">
              <x-ui.form
                  method="POST"
                  :action="route('my.lookups.translations.translate.legal-update', ['update' => $update->id])"
              >
                <x-requirements.summary.lang-selector
                    name="language"
                    @change="$el.closest('form').requestSubmit()"
                />
              </x-ui.form>
            </div>
          </div>
        @endif

        <div class="py-5">
          <turbo-frame id="legal-update-content-{{ $update->id }}">
            @include('partials.notify.legal-update.translate-content')
          </turbo-frame>
        </div>


        <div class="mt-5 flex flex-row justify-between">
          <div>
            @if ($canMarkAsUnderstood)
              <x-ui.form method="post" :action="route('my.notify.legal-updates.understood', ['update' => $update])">
                <x-ui.button type="submit" theme="positive">{{ __('notify.legal_update.i_read_and_understood') }}
                </x-ui.button>
              </x-ui.form>
            @endif
          </div>
          @if ($next)
            <x-ui.button styling="outline" type="link"
                         href="{{ route('my.notify.legal-updates.show', ['update' => $next->id]) }}">
              {{ __('notify.legal_update.next_unread') }}
            </x-ui.button>
          @endif
        </div>

      </x-ui.card>

      {{-- LEFT SIDE --}}
      <div class="grid lg:grid-cols-12 lg:gap-4">
        <div class="lg:col-span-7">

          <x-ui.tabs>
            <x-slot name="nav">
              <x-ui.tab-nav name="streams">{{ __('customer.libryo.applicable_libryo_streams') }}</x-ui.tab-nav>
              @ifOrgAdmin()
              <x-ui.tab-nav name="read_status">{{ __('notify.legal_update.read_and_understood_status') }}
              </x-ui.tab-nav>
              @endifOrgAdmin
            </x-slot>

            <x-ui.tab-content name="streams">
              <div class="pt-5">
                <turbo-frame id="update-applicable-streams-{{ $update->id }}"
                             src="{{ route('my.notify.legal-updates.libryos.index', ['update' => $update]) }}"
                             loading="lazy"></turbo-frame>
              </div>
            </x-ui.tab-content>

            @ifOrgAdmin()
            <x-ui.tab-content name="read_status">
              <div class="pt-5">
                <turbo-frame id="legal-update-user-read-statuses-{{ $update->id }}"
                             src="{{ route('my.notify.legal-updates.users.index', ['update' => $update]) }}"
                             loading="lazy"></turbo-frame>
              </div>
            </x-ui.tab-content>

            @endifOrgAdmin

          </x-ui.tabs>

        </div>

        {{-- RIGHT SIDE --}}
        <div class="lg:col-span-5">
          <x-ui.tabs>
            <x-slot name="nav">

              @ifOrgHasModule('tasks')
              <x-ui.tab-nav name="tasks">{{ __('notify.legal_update.tasks') }}</x-ui.tab-nav>
              @endifOrgHasModule

              @ifOrgHasModule('comments')
              <x-ui.tab-nav name="comments">{{ __('notify.legal_update.comments') }}</x-ui.tab-nav>
              @endifOrgHasModule

            </x-slot>

            @ifOrgHasModule('tasks')
            <x-ui.tab-content name="tasks">
              <div class="pt-5">
                <turbo-frame loading="lazy"
                             src="{{ route('my.tasks.tasks.for.related.index', ['relation' => 'update', 'id' => $update]) }}"
                             id="tasks-for-update-{{ $update->id }}">
                  <x-ui.skeleton/>
                </turbo-frame>
              </div>
            </x-ui.tab-content>
            @endifOrgHasModule

            @ifOrgHasModule('comments')
            <x-ui.tab-content name="comments">
              <div class="pt-5">
                <turbo-frame src="{{ route('my.comments.for.commentable', ['type' => 'update', 'id' => $update->id]) }}"
                             loading="lazy" id="comments-for-update-{{ $update->id }}">
                  <x-ui.skeleton/>
                </turbo-frame>
              </div>
            </x-ui.tab-content>
            @endifOrgHasModule

          </x-ui.tabs>

        </div>
      </div>
    </div>

  @endif


</x-layouts.app>
