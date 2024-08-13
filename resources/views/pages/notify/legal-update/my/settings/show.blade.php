@php use App\Enums\Application\ApplicationType; @endphp
<x-layouts.settings-two-col :header="__('settings.nav.legal_updates')">
    <x-slot name="header">
    <span class="flex items-center">
      <x-ui.icon name="bell" size="10" class="mr-5 text-libryo-gray-600" type="duotone"/>
      <span>{{ $update->title }}</span>
    </span>
    </x-slot>

    <x-slot name="leftCol">
        <x-ui.card>
            <div class="mb-4">
                <h2 class="font-semibold">{{ __('notify.legal_update.related_work') }}</h2>
                <div>{{ $update->work->title ?? '-' }}</div>
            </div>
            @if($update->notifiable)
                <div class="mb-4">
                    <h2 class="font-semibold">{{ __('notify.legal_update.in_terms_of') }}</h2>
                    <div>{{ $update->notifiable->title }}</div>
                </div>
            @endif
        </x-ui.card>

        <x-ui.card>
            <div class="mb-4">
                <h2 class="font-semibold">{{ __('notify.legal_update.notified_against') }}</h2>
                <div>{{ $update->notifyReference->work->title ?? '-' }}</div>
            </div>
        </x-ui.card>
    </x-slot>

    <x-slot name="rightCol">
        <x-ui.card class="no-shadow">
            <div class="font-semibold mb-4">{{ __('notify.legal_update.libraries_notified') }}</div>

            @can('my.notify.legal-update.update')
            <div class="flex items-center space-x-4 mb-4" @closed="location.reload()">
                <div>{{ __('notify.legal_update.notify_by') }}</div>
                <x-ui.modal>
                    <x-slot name="trigger">
                        <x-ui.button size="sm" @click="open = true" theme="primary" styling="outline">
                            <x-ui.icon name="plus" class="mr-2"/>
                            {{ __('compilation.library.libraries') }}
                        </x-ui.button>
                    </x-slot>

                    <div class="lg:w-screen-50">
                        <div class="font-semibold">{{ __('notify.legal_update.select_libraries_to_notify') }}.</div>

                        <turbo-frame
                            id="session-loaded-libraries"
                            loading="lazy"
                            src="{{ route('my.settings.compilation.libraries.session', ['key' => $sessionKey]) }}"
                        >
                            <x-ui.skeleton/>
                        </turbo-frame>

                        <div class="flex justify-end mt-10 space-x-4">
                            <x-ui.form
                                method="post"
                               :action="route('my.settings.legal-updates.libraries.store', ['update' => $update->id])"
                            >
                                <x-ui.button type="submit" theme="primary" styling="outline"
                                >{{ __('actions.save') }}</x-ui.button>
                            </x-ui.form>
                            <x-ui.button styling="outline" @click="location.reload()">
                                {{ __('actions.close') }}
                            </x-ui.button>
                        </div>
                    </div>
                </x-ui.modal>

                <x-ui.modal>
                    <x-slot name="trigger">
                        <x-ui.button size="sm" @click="open = true" theme="primary" styling="outline" class="ml-2">
                            <x-ui.icon name="plus" class="mr-2"/>
                            {{ __('notify.legal_update.sections') }}
                        </x-ui.button>
                    </x-slot>

                    <div class="lg:w-screen-50">

                        <x-ui.form method="post"
                                   :action="route('my.settings.legal-updates.references.store', ['update' => $update->id])"
                        >
                            <x-corpus.reference.multi-reference-selector :app-type="ApplicationType::my()"/>

                            <div class="flex justify-end mt-10 space-x-4">
                                <x-ui.button type="submit" theme="primary" styling="outline"
                                >{{ __('actions.save') }}</x-ui.button>
                                <x-ui.button styling="outline" @click="location.reload()"
                                >{{ __('actions.close') }}</x-ui.button>
                            </div>

                        </x-ui.form>
                    </div>
                </x-ui.modal>

                <x-ui.modal>
                    <x-slot name="trigger">
                        <x-ui.button size="sm" @click="open = true" theme="primary" styling="outline" class="ml-2">
                            <x-ui.icon name="plus" class="mr-2"/>
                            {{ __('settings.nav.libryo_streams') }}
                        </x-ui.button>
                    </x-slot>

                    <div class="lg:w-screen-50">

                        <x-ui.form method="post" :action="route('my.settings.legal-updates.libryos.store', ['update' => $update->id])">
                            <x-customer.libryo.libryo-selector name="libryos[]" id="libryos" required multiple />

                            <div class="flex justify-end mt-10 space-x-4">
                                <x-ui.button type="submit" theme="primary" styling="outline"
                                >{{ __('actions.save') }}</x-ui.button>
                                <x-ui.button styling="outline" @click="location.reload()"
                                >{{ __('actions.close') }}</x-ui.button>
                            </div>

                        </x-ui.form>
                    </div>
                </x-ui.modal>
            </div>
            @endcan


            <div>

                <div>
                    <turbo-frame
                        src="{{ route('my.settings.compilation.library.for.legal-update.index', ['update' => $update]) }}"
                        id="libraries-for-legal-update-{{ $update->id }}"
                    >
                        <x-ui.skeleton/>
                    </turbo-frame>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card class="no-shadow mt-10">
            <div class="font-semibold">{{ __('notify.legal_update.streams_notified') }}</div>

            <div>
                <turbo-frame
                    src="{{ route('my.settings.compilation.libryos.for.legal-update.index', ['update' => $update]) }}"
                    id="libryos-for-legal-update-{{ $update->id }}"
                >
                    <x-ui.skeleton/>
                </turbo-frame>
            </div>
        </x-ui.card>
    </x-slot>

</x-layouts.settings-two-col>
