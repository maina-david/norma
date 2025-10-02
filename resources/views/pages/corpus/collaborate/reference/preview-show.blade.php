@php
    use App\Enums\Corpus\ReferenceLinkType;

@endphp
<x-layouts.collaborate>

    <div class="text-xl mb-3">

        <a class="text-primary" href="{{ route('collaborate.corpus.requirements.preview.index') }}">
            <x-ui.button>
                <x-ui.icon
                           name="chevron-left" />
            </x-ui.button>
        </a>
        <span class="ml-5">{{ $reference->refPlainText->plain_text }}</span>
    </div>

    <x-ui.card>
        <div class="max-h-[70vh] overflow-y-auto">
            <div class="grid grid-cols-1 lg:grid-cols-2">
                <x-ui.tabs x-cloak>
                    <x-slot name="nav">
                        <x-ui.tab-nav class="items-center flex flex-row" name="content">
                            <x-ui.icon name="file-alt" class="mr-2" />
                            {{ __('corpus.reference.requirement_details') }}
                        </x-ui.tab-nav>
                    </x-slot>

                    <x-ui.tab-content name="content" class="border-norma-gray-200 lg:border-r lg:mr-4">
                        <div class="norma-legislation p-3">
                            {!! $reference->htmlContent?->cached_content !!}

                            @if (!$reference->childReferences->isEmpty())
                                @foreach ($reference->childReferences as $childRef)
                                    {!! $childRef->htmlContent?->cached_content !!}
                                @endforeach
                            @endif
                        </div>
                        @if ($reference->work->source)
                            <div class="italic text-norma-gray-600 p-5 text-xs">
                                {!! $reference->work->source->source_content !!}
                            </div>
                        @endif
                    </x-ui.tab-content>
                </x-ui.tabs>


                <x-ui.tabs x-cloak>
                    <x-slot name="nav">
                        <x-ui.tab-nav class="items-center flex flex-row" name="summary">
                            <x-ui.icon name="sticky-note" class="mr-2" />
                            {{ __('requirements.summary.notes') }}
                        </x-ui.tab-nav>

                        <x-ui.tab-nav class="items-center flex flex-row" name="read-with">
                            <x-ui.icon name="object-union" class="mr-2" />
                            <span>{{ __('requirements.read_withs') }}</span>
                            @if ($totalReadWith > 0)
                                <span class="ml-1">({{ $totalReadWith }})</span>
                            @endif
                        </x-ui.tab-nav>


                        <x-ui.tab-nav class="items-center flex flex-row" name="translation">
                            <x-ui.icon name="language" class="mr-2" />
                            {{ __('corpus.reference.translation') }}
                        </x-ui.tab-nav>

                    </x-slot>

                    <x-ui.tab-content name="summary">
                        @if (empty($reference->summary->summary_body ?? ''))
                            <div class="text-center text-norma-gray-600 pt-8">
                                {{ __('requirements.no_notes') }}
                            </div>
                        @else
                            <div class="p-4 wysiwyg-content">
                                <div class="norma-summary mt-5">
                                    <turbo-frame id="summary-content-{{ $reference->summary->reference_id }}"
                                                 target="_top">
                                        {!! $reference->summary->summary_body !!}
                                    </turbo-frame>
                                </div>
                            </div>
                        @endif

                    </x-ui.tab-content>

                    <x-ui.tab-content name="read-with">
                        <div>
                            @if ($totalReadWith === 0)
                                <div class="text-center text-norma-gray-600 pt-8">
                                    {{ __('requirements.no_read_with') }}
                                </div>
                            @endif

                            @if ($amendmentsCount > 0)
                                <x-ui.collapse title-size="text-base"
                                               title="{{ ReferenceLinkType::AMENDMENT->label() }} ({{ $amendmentsCount }})"
                                               flat
                                               class="border-b border-norma-gray-200">
                                    @foreach ($amendments as $amendment)
                                        <div><a class="text-primary"
                                               href="{{ route('collaborate.corpus.requirements.preview.reference.show', ['reference' => $amendment->id]) }} ">{{ $amendment->refPlainText->plain_text }}</a>
                                        </div>
                                    @endforeach
                                </x-ui.collapse>
                            @endif


                            @if ($readWithsCount > 0)
                                <x-ui.collapse title-size="text-base"
                                               title="{{ ReferenceLinkType::READ_WITH->label() }} ({{ $readWithsCount }})"
                                               flat
                                               class="border-b border-norma-gray-200">
                                    @foreach ($readWiths as $readWith)
                                        <div><a class="text-primary mb-5"
                                               href="{{ route('collaborate.corpus.requirements.preview.reference.show', ['reference' => $readWith->id]) }} ">{{ $readWith->refPlainText->plain_text }}</a>
                                        </div>
                                    @endforeach
                                </x-ui.collapse>
                            @endif

                            @if ($reference->raises_consequence_groups_count > 0)
                                <x-ui.collapse title-size="text-base"
                                               title="{{ ReferenceLinkType::CONSEQUENCE->label() }} ({{ $reference->raises_consequence_groups_count }})"
                                               flat class="border-b border-norma-gray-200">
                                    <div>
                                        @foreach ($reference->raisesConsequenceGroups as $consequenceGroup)
                                            <div><a class="text-primary mb-5"
                                                   href="{{ route('collaborate.corpus.requirements.preview.reference.show', ['reference' => $consequenceGroup->id]) }} ">{{ $consequenceGroup->refPlainText->plain_text }}</a>
                                            </div>
                                        @endforeach
                                    </div>
                                </x-ui.collapse>
                            @endif
                        </div>
                    </x-ui.tab-content>

                    @if ($reference->work?->language_code !== 'eng')
                        <x-ui.tab-content name="translation">
                            <div class="p-4">
                                <turbo-frame loading="lazy" id="reference-translation-{{ $reference->id }}"
                                             src="{{ route('my.lookups.translations.translate.reference', ['reference' => $reference->id]) }}">
                                    <x-ui.skeleton />
                                </turbo-frame>
                            </div>
                        </x-ui.tab-content>
                    @endif
                </x-ui.tabs>
            </div>
        </div>
    </x-ui.card>




    <x-ui.card class="mt-5">
        <h3 class="font-bold mb-2">{{ __('compilation.context_question.index_title') }}</h3>
        <div class="flex text-xs flex-wrap">
            @foreach ($reference->contextQuestions as $question)
                <span
                      class="mr-1 mb-1 px-1.5 pt-0.5 pb-0.5 rounded-full flex items-center text-blue-600 bg-blue-100">{{ $question->toQuestion() }}</span>
            @endforeach
        </div>
    </x-ui.card>
    <x-ui.card class="mt-5">
        <h3 class="font-bold mb-2">{{ __('actions.action_area.index_title') }}</h3>
        <div class="flex text-xs flex-wrap">
            @foreach ($reference->actionAreas as $actionArea)
                <span
                      class="mr-1 mb-1 px-1.5 pt-0.5 pb-0.5 rounded-full flex items-center text-cyan-600 bg-cyan-100">{{ $actionArea->title }}</span>
            @endforeach
        </div>
    </x-ui.card>
    <x-ui.card class="mt-5">
        <h3 class="font-bold mb-2">{{ __('ontology.legal_domain.index_title') }}</h3>
        <div class="flex text-xs flex-wrap">
            @foreach ($reference->legalDomains as $domain)
                <span
                      class="mr-1 mb-1 px-1.5 pt-0.5 pb-0.5 rounded-full flex items-center text-green-600 bg-green-100">{{ $domain->title }}</span>
            @endforeach
        </div>
    </x-ui.card>
    <x-ui.card class="mt-5">
        <h3 class="font-bold mb-2">{{ __('geonames.location.index_title') }}</h3>
        <div class="flex text-xs flex-wrap">
            @foreach ($reference->locations as $location)
                <span
                      class="mr-1 mb-1 px-1.5 pt-0.5 pb-0.5 rounded-full flex items-center text-red-600 bg-red-100">{{ $location->title }}</span>
            @endforeach
        </div>
    </x-ui.card>

</x-layouts.collaborate>
