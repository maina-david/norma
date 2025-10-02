@php
use App\Services\Customer\ActiveNormasManager;
@endphp
<div x-data="{ openHelp: false }">
  <div
       x-cloak
       x-show="openHelp"
       x-transition:enter="transform transition ease-in-out duration-500 sm:duration-700"
       x-transition:enter-start="translate-x-full"
       x-transition:enter-end="translate-x-0"
       x-transition:leave="transform transition ease-in-out duration-500 sm:duration-700"
       x-transition:leave-start="translate-x-0"
       x-transition:leave-end="translate-x-full"
       class="w-screen max-w-md fixed inset-y-0 right-0 pl-10 flex">
    <div class="h-full  flex flex-col bg-white shadow-xl overflow-y-scroll">
      <div class="py-6 px-4 sm:px-6 bg-gradient-to-r from-dark-darker to-dark-lighter">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-medium text-white" id="support-over-title">
            {{ __('help.greeting', ['name' => $user->fname]) }}
          </h2>
          <div class="ml-3 h-7 flex items-center">
            <button @click="openHelp = false" type="button"
                    class="rounded-md text-white focus:outline-none focus:ring-2 focus:ring-white">
              <span class="sr-only">Close panel</span>
              <!-- Heroicon name: outline/x -->
              <x-ui.icon name="times" />
            </button>
          </div>
        </div>
        <div class="mt-1">
          <p class="text-sm text-white">
            {{ __('help.lets_help_you') }}
          </p>
        </div>
      </div>
      <div class="relative flex-1 py-6 px-4 sm:px-6">
        <span class="text-sm ">
          {{ __('help.search_success_help_text') }}
        </span>

        <div class="my-12">


          <div class="">
            <x-ui.my.knowledge-base-link />
          </div>

          <x-ui.form data-turbo="false" target="_blank" :action="route('my.help.search.success.portal')" method="get">
            <div class="flex flex-row items-center">
              <div class="w-full">
                <x-ui.input name="term" :placeholder="__('help.search_success_portal')" />
              </div>
            </div>
            <div class="grid justify-items-end mt-3">
              <x-ui.button type="submit" styling="outline" theme="primary">
                {{ __('interface.search') }}
              </x-ui.button>
            </div>
          </x-ui.form>
        </div>

        @if (app(ActiveNormasManager::class)->getActiveOrganisation()->hasLiveChat())
          <div class="my-12">
            <div class="text-lg font-medium mb-5">{{ __('help.want_to_chat') }}</div>
            <div class="text-sm mb-5 cursor-pointer">{{ __('help.want_to_chat_help') }}</div>
            <div
                 @click="window.startLiveChat('{{ $user->fullName }}', '{{ $user->email }}', ['{{ $user->organisations->implode('title', '\',\'') }}']);openHelp = false;">
              <x-ui.button theme="primary" type="button">{{ __('help.want_to_chat_start_chatting') }}
              </x-ui.button>
            </div>
          </div>
        @endif

        <div class="my-12">
          <div class="text-lg font-medium mb-5">{{ __('help.need_training') }}</div>
          <div class="text-sm mb-5">{{ __('help.need_training_help') }}</div>
          <div>
            <x-ui.button target="_blank" href="https://info.norma.com/training" theme="primary" type="link">
              {{ __('help.need_training_sign_up') }}</x-ui.button>
          </div>
        </div>
      </div>

    </div>
  </div>

  <div @click="openHelp = !openHelp"
       class="fixed bottom-10 right-3 p-5 bg-secondary text-white rounded-full rounded-br-none cursor-pointer">
    {{ __('help.help') }}
  </div>

</div>
