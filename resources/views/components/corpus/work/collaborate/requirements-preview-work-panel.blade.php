@props(['work', 'isChild' => false, 'showFlag' => true])
<div {{ $attributes->merge(['class' => 'border-b border-norma-gray-100 pb-2 mb-5 print:mb-1']) }}>
    <div>
        <div class="p-2 flex flex-row justify-between">
            <div>
                <x-corpus.work.work-type :work="$work" />
                <a class="hover:text-primary text-sm font-semibold"
                   href="{{ $work->source_url }}">{{ $work->title }}</a>
                @if ($work->title_translation)
                    <span class="text-norma-gray-500 text-sm">[{{ $work->title_translation }}]</span>
                @endif
            </div>

            @if ($showFlag)
                <div class="">
                    <x-corpus.work.my.work-flags :work="$work" show-multiple-flags />
                </div>
            @endif
        </div>

        @if (!$work->references->isEmpty())
            <div>
                <div>
                    @foreach ($work->references as $ref)
                        <div class="group">
                            <div class="pl-10 flex items-center">

                                <x-ui.icon name="arrow-turn-down-right" size="3"
                                           class="text-norma-gray-400 mr-3" />
                                <a href="{{ route('collaborate.corpus.requirements.preview.reference.show', ['reference' => $ref->id]) }}"
                                   class="cursor-pointer text-primary hover:text-primary-darker text-xs">{{ $ref->refPlainText?->plain_text }}</a>
                            </div>

                            <div x-show="showingContextQuestions" x-cloak>
                                <div class="flex text-xs ml-10 flex-wrap">
                                    @foreach ($ref->contextQuestions as $question)
                                        <span
                                              class="mr-1 mb-1 px-1.5 pt-0.5 pb-0.5 rounded-full flex items-center text-blue-600 bg-blue-100">{{ $question->toQuestion() }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div x-show="showingActionAreas" x-cloak>
                                <div class="flex text-xs ml-10 flex-wrap">
                                    @foreach ($ref->actionAreas as $actionArea)
                                        <span
                                              class="mr-1 mb-1 px-1.5 pt-0.5 pb-0.5 rounded-full flex items-center text-cyan-600 bg-cyan-100">{{ $actionArea->title }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div x-show="showingDomains" x-cloak>
                                <div class="flex text-xs ml-10 flex-wrap">
                                    @foreach ($ref->legalDomains as $domain)
                                        <span
                                              class="mr-1 mb-1 px-1.5 pt-0.5 pb-0.5 rounded-full flex items-center text-green-600 bg-green-100">{{ $domain->title }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div x-show="showingLocations" x-cloak>
                                <div class="flex text-xs ml-10 flex-wrap">
                                    @foreach ($ref->locations as $location)
                                        <span
                                              class="mr-1 mb-1 px-1.5 pt-0.5 pb-0.5 rounded-full flex items-center text-red-600 bg-red-100">{{ $location->title }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @if ($work->relationLoaded('children') && !$work->children->isEmpty())
        @foreach ($work->children as $child)
            <div class="pl-10">
                <x-corpus.work.collaborate.requirements-preview-work-panel :work="$child" :is-child="true"
                                                                           :show-flag="false" />
            </div>
        @endforeach
    @endif
</div>
