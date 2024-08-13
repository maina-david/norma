@php
  use App\Enums\Corpus\ReferenceType;
  use App\Enums\Corpus\ReferenceStatus;
@endphp

<div
    @if(ReferenceType::citation()->is($reference->type))
      data-volume="{{ $reference->volume }}"
      x-data="{selectors: {{ json_encode($reference->refSelector?->selectors ?? []) }} }"
      @click="function () { evaluateSelector($el, this.selectors) }"
    @endif
    class="w-full{{ $active ? ' active' : '' }}"
    style="padding-left:{{ $reference->level }}rem;"
    id="reference_{{ $reference->id }}"
>

  <div
      class="w-full rounded border shadow-sm bg-white {{ ReferenceStatus::pending()->is($reference->status) ? 'border-secondary' : 'border-libryo-gray-200' }}"
  >
    <div class="rounded text-sm">
      <div class="flex items-center font-semibold group">
        <a
          href="{{ route('collaborate.work-expressions.creation.references.' . ($active ? 'deactivate' : 'activate'), ['expression' => $expression->id, 'reference' => $reference->id])  }}"
          class="flex-grow py-2 px-4 mr-4"
        >
          @if ($reference->refRequirement || $reference->requirementDraft)
            <span class="mr-2 tippy" data-tippy-content="{{ __('corpus.reference.has_requirements') }}">
              <x-ui.icon name="octagon-exclamation" size="4"
                         class="{{ $reference->requirementDraft ? 'text-negative' : ($reference->refRequirement ? 'text-primary' : 'text-libryo-gray-200') }}" />
            </span>
          @endif
          <span class="flex-grow text-primary">
            {{ $reference->refPlainText?->plain_text }}
          </span>
        </a>
        <div class="flex-shrink-0 pr-1 hidden group-hover:flex">
          @can('collaborate.corpus.reference.create')
            <x-ui.form method="POST" :action="route('collaborate.work-expressions.creation.references.indent', ['expression' => $expression->id, 'reference' => $reference->id])">
              <x-ui.button @click.stop="" type="submit" styling="flat" theme="primary" class="tippy" data-tippy-content="{{ __('corpus.reference.increase_level') }}">
                <x-ui.icon name="indent" size="4" />
              </x-ui.button>
            </x-ui.form>

            <x-ui.form method="POST" :action="route('collaborate.work-expressions.creation.references.outdent', ['expression' => $expression->id, 'reference' => $reference->id])">
              <x-ui.button @click.stop="" type="submit" styling="flat" theme="primary" class="tippy" data-tippy-content="{{ __('corpus.reference.decrease_level') }}">
                <x-ui.icon name="outdent" size="4" />
              </x-ui.button>
            </x-ui.form>

            <x-ui.form method="POST" :action="route('collaborate.work-expressions.creation.references.insert-below', ['expression' => $expression->id, 'reference' => $reference->id])">
              <x-ui.button @click.stop="" type="submit" styling="flat" theme="primary" class="tippy" data-tippy-content="{{ __('corpus.reference.insert_one_below') }}">
                <x-ui.icon name="plus-minus" size="4" />
              </x-ui.button>
            </x-ui.form>
          @endcan

          @can('collaborate.corpus.reference.delete')
            <x-ui.delete-button @click.stop="" styling="flat" :route="route('collaborate.work-expressions.creation.references.destroy', ['reference' => $reference->id])">
              <x-ui.icon name="trash-alt" size="4" />
            </x-ui.delete-button>
          @endcan
        </div>

        <div class="flex-shrink-0 flex items-center pr-2">
          <input
            x-bind:checked="selected.includes({{ $reference->id }})"
            @click.stop="toggleSelection({{ $reference->id }})"
            type="checkbox"
            class="mr-2 h-4 w-4 text-primary focus:ring-primary border-libryo-gray-300 rounded"
          >
        </div>
      </div>

      @if($active)
        <x-turbo::frame
          id="reference_{{ $reference->id }}_content"
          :src="route('collaborate.work-expressions.creation.references.content.show', ['expression' => $expression->id, 'reference' => $reference->id])"
        >
          <x-ui.skeleton />
        </x-turbo::frame>
      @endif

    </div>
  </div>


</div>

<div id="reference_{{ $reference->id }}_after"></div>
