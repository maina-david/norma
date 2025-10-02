@php
use App\Enums\Application\ApplicationType;
@endphp

<x-layouts.settings>
  <x-slot name="header">
    <div class="flex items-center">
      <x-ui.icon name="check-square" size="10" class="mr-5 text-norma-gray-600" />
      <span>{{ __('settings.nav.compilation') }}</span>
    </div>

  </x-slot>

  <div>
    <x-ui.card>

      <div class="flex flex-row justify-between">

        <div class="flex flex-row justify-between">

          <x-ui.modal>
            <x-slot name="trigger">
              <x-ui.button @click="open = true" theme="primary" styling="outline">
                <x-ui.icon name="plus" class="mr-2" />
                {{ __('compilation.manual_compilation.add_works') }}
                ({{ $works->count() }})
              </x-ui.button>
            </x-slot>

            <div class="w-screen-50">
              <turbo-frame src="{{ route('my.settings.compilation.works.for.manual.compilation') }}"
                           loading="lazy"
                           id="works-for-manual-compilation">
                <x-ui.skeleton />
              </turbo-frame>
            </div>
          </x-ui.modal>

          @if ($works->count() > 0)
            <div>
              <x-ui.form method="delete" :action="route('my.settings.compilation.works.for.manual.compilation.remove')">
                <x-ui.button type="submit" class="ml-3 tippy" theme="primary"
                             data-tippy-content="{{ __('compilation.manual_compilation.clear_works') }}">
                  <x-ui.icon name="multiply"></x-ui.icon>
                </x-ui.button>
              </x-ui.form>
            </div>
          @endif
        </div>


        <div class="flex flex-row">
          <div @closed="location.reload()">
            <x-ui.modal>
              <x-slot name="trigger">
                <x-ui.button @click="open = true" theme="primary" styling="outline">
                  <x-ui.icon name="plus" class="mr-2" />
                  {{ __('compilation.manual_compilation.add_libraries') }}
                  ({{ $libraries->count() }})
                </x-ui.button>
              </x-slot>

              <div class="w-screen-50">
                <turbo-frame id="session-loaded-libraries"
                             loading="lazy"
                             src="{{ route('my.settings.compilation.libraries.session', ['key' => 'compilation']) }}">
                  <x-ui.skeleton />
                </turbo-frame>
              </div>
            </x-ui.modal>
          </div>

          <div @closed="location.reload()">
            <x-ui.modal>
              <x-slot name="trigger">
                <x-ui.button @click="open = true" theme="primary" styling="outline" class="ml-2">
                  <x-ui.icon name="plus" class="mr-2" />
                  {{ __('compilation.manual_compilation.add_libraries_by_section') }}
                </x-ui.button>
              </x-slot>

              <div class="w-screen-50">
                <x-ui.form method="post"
                           :action="route('my.settings.compilation.libraries.session.add.by.references', ['key' => 'compilation'])">
                  <x-corpus.reference.multi-reference-selector :app-type="ApplicationType::my()" />
                  <x-ui.button type="submit" class="mt-10" theme="primary">
                    {{ __('compilation.manual_compilation.add_libraries_by_section') }}
                  </x-ui.button>
                </x-ui.form>
              </div>
            </x-ui.modal>
          </div>

          @if ($libraries->count() > 0)
            <div>
              <x-ui.form method="delete"
                         :action="route('my.settings.compilation.libraries.session.remove', ['key' => 'compilation'])">
                <x-ui.button type="submit" class="ml-3 tippy" theme="primary"
                             data-tippy-content="{{ __('compilation.manual_compilation.clear_libraries') }}">
                  <x-ui.icon name="multiply"></x-ui.icon>
                </x-ui.button>
              </x-ui.form>
            </div>
          @endif
        </div>

      </div>
    </x-ui.card>


    {{-- compile all works --}}
    <x-ui.card class="mt-10">
      <div class="flex justify-end">
        <div>
          <div>
            <turbo-frame id="manual-compilation-all-works">
              <a
                 href="{{ route('my.settings.compilation.manual.all.works.add') }}">
                <x-ui.icon name="check-circle" size="8" class="text-positive cursor-pointer" />
              </a>
            </turbo-frame>
          </div>
          <div>
            <turbo-frame id="manual-compilation-all-works-remove">
              <a
                 href="{{ route('my.settings.compilation.manual.all.works.remove') }}">
                <x-ui.icon name="minus-circle" size="8" class="text-negative cursor-pointer" />
              </a>
            </turbo-frame>
          </div>
        </div>
      </div>
    </x-ui.card>


    @foreach ($works as $work)
      <x-ui.card x-data="{ showingWork: false}" class="mt-5">
        <table class="min-w-full divide-y divide-norma-gray-200">
          <thead>
            <tr>
              <th>
                <div x-cloak class="flex flex-row items-center">
                  <div @click="showingWork = !showingWork" class="mr-5 cursor-pointer">
                    <x-ui.icon x-show="!showingWork" name="plus-square"></x-ui.icon>
                    <x-ui.icon x-show="showingWork" name="minus-square"></x-ui.icon>
                  </div>
                  <div class="text-base font-bold">{{ $work->title }}</div>
                </div>
              </th>
              <th width="50">
                @if ($libraries->count() > 0)
                  <div class="flex justify-end">
                    <turbo-frame id="manual-compilation-work-{{ $work->id }}">
                      <a
                         href="{{ route('my.settings.compilation.manual.work.add', ['work' => $work]) }}">
                        <x-ui.icon name="check-circle" size="7" class="text-positive cursor-pointer" />
                      </a>
                    </turbo-frame>
                  </div>
                @endif
              </th>
              <th width="50">
                @if ($libraries->count() > 0)
                  <div class="flex justify-end">
                    <turbo-frame id="manual-compilation-work-{{ $work->id }}">
                      <a
                         href="{{ route('my.settings.compilation.manual.work.add', ['work' => $work]) }}">
                        <x-ui.icon name="minus-circle" size="7" class="text-negative cursor-pointer" />
                      </a>
                    </turbo-frame>
                  </div>
                @endif
              </th>
            </tr>
          </thead>
          <tbody x-show="showingWork" x-cloak>
            <tr></tr>

            @foreach ($work->references as $reference)
              <tr>
                <td>
                  <div x-data="{showingReference: false}" class="py-1">
                    <div x-cloak class="flex flex-row items-center">
                      <div @click="showingReference = !showingReference" class="mr-5 cursor-pointer">
                        <x-ui.icon x-show="!showingReference" name="plus-square"></x-ui.icon>
                        <x-ui.icon x-show="showingReference" name="minus-square"></x-ui.icon>
                      </div> lc
                      <div>{{ $reference->refPlainText?->plain_text }}</div>
                    </div>
                    <div x-show="showingReference">
                      <x-ui.tabs>
                        <x-slot name="nav">
                          <x-ui.tab-nav name="text">{{ __('compilation.manual_compilation.full_text') }}
                          </x-ui.tab-nav>
                          <x-ui.tab-nav name="libraries">{{ __('compilation.library.libraries') }}
                          </x-ui.tab-nav>
                        </x-slot>

                        <div class="py-5">
                          <x-ui.tab-content name="text">
                            <div>
                              <p>{!! $reference->htmlContent?->cached_content !!}</p>
                            </div>
                          </x-ui.tab-content>

                          <x-ui.tab-content name="libraries">
                            <turbo-frame id="libraries-for-reference-{{ $reference->id }}" loading="lazy"
                                         src="{{ route('my.settings.compilation.library.for.reference.index', ['reference' => $reference]) }}">
                              <x-ui.skeleton />
                            </turbo-frame>
                          </x-ui.tab-content>
                        </div>

                      </x-ui.tabs>
                    </div>
                  </div>
                </td>

                <td>
                  @if ($libraries->count() > 0)
                    <div class="flex justify-end">
                      <turbo-frame id="manual-compilation-reference-{{ $reference->id }}">
                        <a
                           href="{{ route('my.settings.compilation.manual.reference.add', ['reference' => $reference]) }}">
                          <x-ui.icon name="check-circle" class="text-positive cursor-pointer" />
                        </a>
                      </turbo-frame>
                    </div>
                  @endif
                </td>

                <td>
                  @if ($libraries->count() > 0)
                    <div class="flex justify-end">
                      <turbo-frame id="manual-compilation-reference-remove-{{ $reference->id }}">
                        <a
                           href="{{ route('my.settings.compilation.manual.reference.remove', ['reference' => $reference]) }}">
                          <x-ui.icon name="minus-circle" class="text-negative cursor-pointer" />
                        </a>
                      </turbo-frame>
                    </div>
                  @endif
                </td>
              </tr>
            @endforeach

          </tbody>
        </table>
      </x-ui.card>
    @endforeach


  </div>

</x-layouts.settings>
