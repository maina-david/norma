@php use App\Models\Collaborators\Team; @endphp
@php use App\Services\Auth\ImpersonationManager; @endphp
<nav class="bg-navbar border-b border-libryo-gray-200 relative shrink-0 z-20">
  <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex h-16" x-data="{ sidebar: false }">
      <div class="-ml-2 mr-2 flex items-center sm:hidden">
        <button @click="sidebar = !sidebar" type="button"
                class="bg-transparent inline-flex items-center justify-center p-2 rounded-md text-libryo-gray-100 hover:text-libryo-gray-100 hover:bg-libryo-gray-100 focus:outline-none focus:bg-transparent active:bg-transparent"
                aria-controls="mobile-menu" aria-expanded="false">
          <span class="sr-only">Open main menu</span>
          <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
               stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
          <svg class="hidden h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
               stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </button>
      </div>

      <div class="flex grow">
        <div class="shrink-0 flex items-center text-libryo-gray-900">
          <x-logo.collaborate class="h-8"/>
        </div>
        <div
            class="bg-navbar h-full sm:h-auto sm:bg-transparent w-80 sm:w-auto fixed sm:relative pt-2 sm:pt-0 mt-16 sm:mt-0 flex flex-col sm:flex-row sm:-my-px sm:ml-6 sm:space-x-8 sm:left-0 p-2 sm:p-0"
            x-bind:class="sidebar ? 'left-0' : '-left-full'">

          <x-ui.nav-item class="w-full sm:w-auto" route="collaborate.dashboard">{{ __('nav.dashboard') }}
          </x-ui.nav-item>

          @collaborateViewAny(['workflows.task'])
          <x-ui.nav-item class="w-full sm:w-auto" route="collaborate.tasks.index">{{ __('nav.workflows') }}
          </x-ui.nav-item>
          @endcollaborateViewAny

          @collaborateViewAny(['corpus.catalogue-work', 'corpus.work', 'notify.legal-update', 'corpus.doc.for-updates', 'corpus.doc.update-candidate.manage-without-task', 'corpus.catalogue-doc'])
          <x-ui.dropdown-nav-item>
            <x-slot name="nav">
              {{ __('nav.corpus') }}
            </x-slot>

            @collaborateViewAny(['corpus.catalogue-doc'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.corpus.catalogue-docs.index">
              {{ __('nav.catalogue_docs') }}
            </x-ui.nav-item>
            @endcollaborateViewAny

            @collaborateViewAny(['corpus.catalogue-work'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.catalogue-works.index">
              {{ __('nav.catalogue_works') }}
            </x-ui.nav-item>
            @endcollaborateViewAny

            @collaborateViewAny(['corpus.work'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.works.index">{{ __('nav.legislation') }}
            </x-ui.nav-item>
            @endcollaborateViewAny

            @collaborateViewAny(['notify.legal-update'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.legal-updates.index">
              {{ __('nav.legal_updates') }}
            </x-ui.nav-item>
            @endcollaborateViewAny

            @collaborateViewAny(['corpus.doc.for-updates'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.corpus.docs.for-update.index">
              {{ __('nav.docs_for_updates') }}
            </x-ui.nav-item>
            @endcollaborateViewAny


            @can('collaborate.corpus.doc.update-candidate.manage-without-task')
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.docs-for-update.tasks.index" :route-params="['task' => '0']">
              {{ __('corpus.doc.manage_updates') }}
            </x-ui.nav-item>
            @endcan

          </x-ui.dropdown-nav-item>
          @endcollaborateViewAny

          @collaborateViewAny([
              'actions.action-area',
              'ontology.legal-domain',
              'ontology.category',
              'compilation.context-question',
              'assess.assessment-item',
              'arachno.source',
              'ontology.tag',
              'lookups.canned-response',
              'geonames.location',
              'requirements.consequence'
          ])
          <x-ui.dropdown-nav-item>
            <x-slot name="nav">
              {{ __('nav.metadata') }}
            </x-slot>

            @collaborateViewAny(['actions.action-area'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.action-areas.index">
              {{ __('nav.action_areas') }}
            </x-ui.nav-item>
            @endcollaborateViewAny

            @collaborateViewAny(['assess.assessment-item'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.assessment-items.index">
              {{ __('nav.assessment_items') }}
            </x-ui.nav-item>
            @endcollaborateViewAny

            @collaborateViewAny(['lookups.canned-response'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.canned-responses.index">
              {{ __('nav.canned_responses') }}
            </x-ui.nav-item>
            @endcollaborateViewAny

            @collaborateViewAny(['requirements.consequence'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.consequences.index">
              {{ __('nav.consequences') }}
            </x-ui.nav-item>
            @endcollaborateViewAny

            @collaborateViewAny(['compilation.context-question'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.context-questions.index">
              {{ __('nav.context_questions') }}
            </x-ui.nav-item>
            @endcollaborateViewAny

            @collaborateViewAny(['geonames.location'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.jurisdictions.index">
              {{ __('nav.jurisdictions') }}
            </x-ui.nav-item>
            @endcollaborateViewAny

            @collaborateViewAny(['ontology.legal-domain'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.legal-domains.index">
              {{ __('nav.legal_domains') }}
            </x-ui.nav-item>
            @endcollaborateViewAny

            @collaborateViewAny(['arachno.source'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.sources.index">{{ __('nav.sources') }}
            </x-ui.nav-item>
            @endcollaborateViewAny

            @collaborateViewAny(['ontology.tag'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.tags.index">{{ __('nav.tags') }}
            </x-ui.nav-item>
            @endcollaborateViewAny

            @collaborateViewAny(['ontology.category'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.ontology.categories.index">
              {{ __('nav.topics') }}
            </x-ui.nav-item>
            @endcollaborateViewAny

            @collaborateViewAny(['system.processing-job'])
            <div class="w-full border-b border-libryo-gray-200"></div>
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.processing-jobs.index">
              {{ __('nav.processing_jobs') }}
            </x-ui.nav-item>
            @endcollaborateViewAny


          </x-ui.dropdown-nav-item>
          @endcollaborateViewAny


          @collaborateViewAny([
              'collaborators.collaborator',
              'collaborators.collaborator-application',
              'collaborators.team',
              'collaborators.group',
              'payments.payment',
              'collaborators.team.payment-request.outstanding',
              'workflows.task-application'
          ])
          <x-ui.dropdown-nav-item>
            <x-slot name="nav">
              {{ __('nav.hr') }}
            </x-slot>

            @collaborateViewAny(['workflows.task-application'])
            <x-ui.dropdown-header>{{ __('nav.tasks') }}</x-ui.dropdown-header>
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.task-applications.index">
              {{ __('nav.applications') }}
            </x-ui.nav-item>
            @endcollaborateViewAny

            @collaborateViewAny(['collaborators.collaborator', 'collaborators.group', 'collaborators.team'])
            <x-ui.dropdown-header>{{ __('nav.collaborators') }}</x-ui.dropdown-header>
            @endcollaborateViewAny

            @collaborateViewAny(['collaborators.collaborator-application'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.collaborator-applications.index">
              {{ __('nav.collaborator_applications') }}
            </x-ui.nav-item>
            @endcollaborateViewAny

            @collaborateViewAny(['collaborators.collaborator'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.collaborators.index">
              {{ __('nav.collaborators') }}
            </x-ui.nav-item>
            @endcollaborateViewAny

            @collaborateViewAny(['collaborators.group'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.groups.index">{{ __('nav.groups') }}
            </x-ui.nav-item>
            @endcollaborateViewAny

            @collaborateViewAny(['collaborators.team'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.teams.index">{{ __('nav.teams') }}
            </x-ui.nav-item>
            @endcollaborateViewAny

            @collaborateViewAny(['payments.payment', 'collaborators.team.payment-request.outstanding'])
            <x-ui.dropdown-header>{{ __('nav.payments') }}</x-ui.dropdown-header>
            @endcollaborateViewAny

            @collaborateViewAny(['payments.payment'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.payments.payments.index">
              {{ __('nav.payments_all') }}
            </x-ui.nav-item>
            @endcollaborateViewAny

            @collaborateViewAny(['collaborators.team.payment-request.outstanding'])
            <x-ui.nav-item class="whitespace-nowrap" block
                           route="collaborate.collaborators.teams.payment-requests.outstanding.index">
              {{ __('nav.payments_outstanding') }}
            </x-ui.nav-item>
            @endcollaborateViewAny

          </x-ui.dropdown-nav-item>
          @endcollaborateViewAny


          @collaborateViewAny([
              'collaborators.group',
              'collaborators.team',
              'auth.role',
              'workflows.board',
              'workflows.rating-group',
              'workflows.rating-type',
              'workflows.task-type',
              'workflows.monitoring-task-config',
              'notify.notification-action',
              'notify.notification-handover',
          ])
          <x-ui.dropdown-nav-item>
            <x-slot name="nav">
              {{ __('nav.admin') }}
            </x-slot>

            @collaborateViewAny(['notify.notification-action', 'notify.notification-handover'])
            <x-ui.dropdown-header>{{ __('corpus.doc.manage_updates') }}</x-ui.dropdown-header>

            @collaborateViewAny(['notify.notification-action'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.notification-actions.index">{{ __('notify.notification_action.index_title') }}
            </x-ui.nav-item>
            @endcollaborateViewAny

            @collaborateViewAny(['notify.notification-handover'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.notification-handovers.index">{{ __('notify.notification_handover.index_title') }}
            </x-ui.nav-item>
            @endcollaborateViewAny

            @endcollaborateViewAny

            @collaborateViewAny(['workflows.board', 'workflows.rating-group', 'workflows.rating-type', 'workflows.task-type', 'workflows.monitoring-task-config'])
            <x-ui.dropdown-header>{{ __('nav.workflows') }}</x-ui.dropdown-header>

            @collaborateViewAny(['workflows.monitoring-task-config'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.monitoring-task-configs.index">{{ __('workflows.monitoring_task_config.index_title') }}
            </x-ui.nav-item>
            @endcollaborateViewAny

            @collaborateViewAny(['workflows.board'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.boards.index">{{ __('nav.boards') }}
            </x-ui.nav-item>
            @endcollaborateViewAny

            @collaborateViewAny(['workflows.rating-group'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.rating-groups.index">
              {{ __('nav.rating_groups') }}
            </x-ui.nav-item>
            @endcollaborateViewAny

            @collaborateViewAny(['workflows.rating-type'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.rating-types.index">
              {{ __('nav.rating_types') }}</x-ui.nav-item>
            @endcollaborateViewAny

            @collaborateViewAny(['workflows.task-type'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.task-types.index">
              {{ __('nav.task_types') }}</x-ui.nav-item>
            @endcollaborateViewAny
            @endcollaborateViewAny

            @collaborateViewAny(['auth.role'])
            <x-ui.dropdown-header>{{ __('nav.users_groups') }}</x-ui.dropdown-header>

            @collaborateViewAny(['auth.role'])
            <x-ui.nav-item class="whitespace-nowrap" block route="collaborate.roles.index">{{ __('nav.roles') }}
            </x-ui.nav-item>
            @endcollaborateViewAny

            @endcollaborateViewAny

          </x-ui.dropdown-nav-item>
          @endcollaborateViewAny

        </div>
      </div>

      <div class="flex items-center">
        <button type="button"
                class="mr-4 bg-white p-1 rounded-full text-libryo-gray-400 hover:text-libryo-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
          <span class="sr-only">{{ __('nav.view_notifications') }}</span>

          <svg class="h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
               stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
          </svg>
        </button>

        <x-ui.dropdown position="left">
          <x-slot name="trigger">
            <button type="button"
                    class="max-w-xs bg-white flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    id="user-menu-button" aria-expanded="false" aria-haspopup="true">
              <span class="sr-only">Open user menu</span>
              <x-ui.user-avatar :user="$user"></x-ui.user-avatar>
            </button>
          </x-slot>

          <div class="whitespace-nowrap w-48">

            <a href="{{ route('collaborate.profile.show') }}"
               class="hover:text-primary block px-4 py-2 text-sm text-libryo-gray-700"
               role="menuitem" tabindex="-1" id="user-menu-item-0">
              {{ __('nav.profile') }}
            </a>

            @can('manage', Team::class)
              <a href="{{ route('collaborate.collaborators.teams.billing.edit') }}"
                 class="block px-4 py-2 text-sm text-libryo-gray-700"
                 role="menuitem" tabindex="-1" id="user-menu-item-0">
                {{ __('nav.billing') }}
              </a>
            @endcan

            <a href="https://learn.libryo.com/"
               target="_blank"
               class="hover:text-primary block px-4 py-2 text-sm text-libryo-gray-700"
               role="menuitem" tabindex="-1" id="user-menu-item-0">
              {{ __('nav.learn') }}
            </a>

            <a href="https://forms.clickup.com/f/4axm9-1334/DS1W7A03NO5BU0BUYG"
               target="_blank"
               class="hover:text-primary block px-4 py-2 text-sm text-libryo-gray-700"
               role="menuitem" tabindex="-1" id="user-menu-item-0">
              {{ __('nav.support') }}
            </a>

            @if (app(ImpersonationManager::class)->isImpersonating())
              <a
                  href="{{ route('collaborate.collaborator.impersonate.leave') }}"
                  class="hover:text-primary text-libryo-gray-700 block px-4 py-2 text-sm" role="menuitem" tabindex="-1"
                  id="menu-item-0">
                {{ __('auth.user.leave_impersonation') }}
              </a>
            @endif

            <x-ui.form method="post" action="{{ route('logout') }}">
              <button class="hover:text-primary px-4 py-2 text-sm text-libryo-gray-700 w-full text-left" role="menuitem"
                      tabindex="-1">
                Sign out
              </button>
            </x-ui.form>
          </div>

        </x-ui.dropdown>

      </div>
    </div>
  </div>
</nav>
