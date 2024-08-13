<tr>
  <td class="pl-4">
    <x-ui.input
      class="work_{{ $work->id }}_child app-action-selection"
      type="checkbox"
      label=""
      id="reference_{{ $reference->id }}"
      name="references[]"
      :checkbox-value="$reference->id"
      @change="validateActionVisibility()"
    />
  </td>
  <td class="px-1 py-4 whitespace-normal">
    <div>
      <div class="text-sm text-primary">
        <x-ui.link
          href="{{ route('my.corpus.references.show', ['reference' => $reference->id]) }}"
          target="_blank">
          {{ $reference->refPlainText->plain_text ?? '-' }}
        </x-ui.link>
      </div>
      <div></div>
    </div>
  </td>
  @if($libryo)
    <td class="w-40">
      @if($reference->libryo_recommended || in_array($reference->work_id, $siteWorks))
        <x-ui.modal>
          <x-slot name="trigger">
            <div class="flex items-center justify-center">
              <div class="p-2 cursor-pointer" @click="open = true">
                <x-ui.icon name="check-circle" class="text-primary" />
              </div>
            </div>
          </x-slot>

          <div class="px-4 pb-4 w-screen max-w-3xl">
            <turbo-frame
                loading="lazy"
                id="applicability-for-{{ $reference->id }}"
                src="{{ route('my.applicability-reference.index', ['reference' => $reference->id]) }}"
            >
              <x-ui.skeleton/>
            </turbo-frame>
          </div>
        </x-ui.modal>
      @endif
    </td>
    <td class="w-40">
      <livewire:compilation.context-question.applicability-requirement-changer
        :libryo-ids="[$libryo->id]"
        :reference-ids="[$reference->id]"
        :in-libryo="$reference->included_in_stream"
        :key="sprintf('%d-%d-%s-%s', $libryo->id, $reference->id, $reference->included_in_stream ? 'y' : 'n', Str::random(64))"
      />
    </td>
  @else
    <td class="w-40">
      <div class="text-primary text-center">{{ $reference->included_in_stream }}</div>
    </td>
  @endif
</tr>

