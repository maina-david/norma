@php
  use App\Enums\Corpus\ReferenceType;
  use App\Enums\Corpus\ReferenceStatus;
@endphp

<div
     class="w-full{{ $active ? ' active' : '' }} pl-1"
     id="reference_{{ $reference->id }}">

  <div
       class="w-full rounded border shadow-sm bg-white {{ $reference->contentDraft ? 'border-secondary' : 'border-libryo-gray-200' }}">
    <div class="rounded text-sm">
      <div class="flex items-center font-semibold group">
        <a
           href="{{ route('collaborate.work-expressions.identify.references.' . ($active ? 'deactivate' : 'activate'), ['expression' => $expression->id, 'reference' => $reference->id]) }}"
           class="flex-grow py-2 px-4 mr-4 {{ $reference->latestTocItem ? 'tippy' : '' }}"
           data-tippy-allowHTML="true"
           data-tippy-content="{{ $reference->latestTocItem?->parent?->parent?->parent ? $reference->latestTocItem->parent->parent->parent->label . '<br/>' : '' }} {{ $reference->latestTocItem?->parent?->parent ? $reference->latestTocItem->parent->parent->label . '<br/>' : '' }} {{ $reference->latestTocItem?->parent ? $reference->latestTocItem->parent->label : '' }}"
           data-tippy-delay="400">

          @if ($reference->refRequirement || $reference->requirementDraft)
            <span class="mr-2 tippy" data-tippy-content="{{ __('corpus.reference.has_requirements') }}">
              <x-ui.icon name="octagon-exclamation" size="4"
                         class="{{ $reference->requirementDraft ? 'text-negative' : ($reference->refRequirement ? 'text-primary' : 'text-libryo-gray-200') }}" />
            </span>
          @endif
          <span class="flex-grow text-primary">
            {{ $reference->contentDraft ? $reference->contentDraft->title : $reference->refPlainText?->plain_text }}
          </span>
        </a>
        <div class="flex-shrink-0 pr-1 hidden group-hover:flex">

          {{-- MOVE UP BUTTON --}}
          @if ($reference->position > 1)
            <x-ui.form method="POST" :action="route('collaborate.work-expressions.identify.references.move-up', [
                'expression' => $expression->id,
                'reference' => $reference->id,
            ])">
              <input type="hidden" name="redirect_back" value="{{ url()->full() }}">
              <x-ui.button @click.stop="" type="submit" styling="flat" theme="gray" class="tippy"
                           data-tippy-content="{{ __('corpus.reference.move_up') }}">
                <x-ui.icon name="arrow-up" size="4" />
              </x-ui.button>
            </x-ui.form>
          @endif

          {{-- MOVE DOWN BUTTON --}}
          <x-ui.form method="POST" :action="route('collaborate.work-expressions.identify.references.move-down', [
              'expression' => $expression->id,
              'reference' => $reference->id,
          ])">
            <input type="hidden" name="redirect_back" value="{{ url()->full() }}">
            <x-ui.button @click.stop="" type="submit" styling="flat" theme="gray" class="tippy"
                         data-tippy-content="{{ __('corpus.reference.move_down') }}">
              <x-ui.icon name="arrow-down" size="4" />
            </x-ui.button>
          </x-ui.form>

          @can('collaborate.corpus.reference.create')
            <x-ui.form method="POST" :action="route('collaborate.work-expressions.identify.references.insert-below', [
                'expression' => $expression->id,
                'reference' => $reference->id,
            ])">
              <input type="hidden" name="redirect_back" value="{{ url()->full() }}">
              <x-ui.button @click.stop="" type="submit" styling="flat" theme="primary" class="tippy ml-5"
                           data-tippy-content="{{ __('corpus.reference.insert_one_below') }}">
                <x-ui.icon name="plus-minus" size="4" />
              </x-ui.button>
            </x-ui.form>
          @endcan

          @if ($reference->contentDraft && auth()->user()->can('collaborate.corpus.reference.delete'))
            <x-ui.delete-button @click.stop="" styling="flat" :route="route('collaborate.work-expressions.identify.references.destroy', [
                'reference' => $reference->id,
            ])">

              <x-slot:formBody>
                <input type="hidden" name="redirect_back" value="{{ url()->full() }}">
              </x-slot:formBody>

              <x-ui.icon name="trash-alt" size="4" />
            </x-ui.delete-button>
          @endif

          @if (
              !$reference->contentDraft &&
                  auth()->user()->can('collaborate.corpus.reference.request-update'))
            <x-ui.form method="POST" :action="route('collaborate.work-expressions.identify.references.request-update', [
                'expression' => $expression->id,
                'reference' => $reference->id,
            ])">
              <input type="hidden" name="redirect_back" value="{{ url()->full() }}">
              <x-ui.button @click.stop="" type="submit" styling="flat" theme="gray" class="tippy"
                           data-tippy-content="{{ __('corpus.reference.request_update') }}">
                <x-ui.icon name="code-compare" size="4" />
              </x-ui.button>
            </x-ui.form>
          @endif
        </div>
      </div>

      @if ($active)
        <x-turbo::frame
          id="reference_{{ $reference->id }}_content"
          :src="route('collaborate.work-expressions.identify.references.content.show', [
            'expression' => $expression->id,
            'reference' => $reference->id,
          ])"
        >
          <x-ui.skeleton />
        </x-turbo::frame>
      @endif

    </div>
  </div>


</div>
