@props(['labelSuffix', 'action', 'buttonTheme', 'expressionId', 'isOpen' => false, 'checkFirst' => false, 'checkSuffix' => ''])

<div id="{{ $action }}" x-data="{checked: {{ json_encode(!$checkFirst) }}}">
  @if($checkFirst)

      <x-ui.form x-show="!checked" method="post" :action="route('collaborate.work-expressions.references.actions.check', ['expression' => $expressionId])">
        <input type="hidden" name="action" value="{{ $action }}" >
        <input type="hidden" name="label_suffix" value="{{ $labelSuffix }}" >

        <template x-for="(val,selRef) in selectedRefs" :key="selRef">
          <input type="hidden" x-bind:name="'reference_' + selRef" x-bind:value="val">
        </template>

        <x-ui.button
            styling="outline"
            :theme="$buttonTheme ?? 'primary'"
            type="submit"
            size="md"
            {{ $attributes }}
        >
          <span>{{ __('corpus.reference.' . $labelSuffix) }}</span>
        </x-ui.button>
      </x-ui.form>

  @endif

  <x-ui.confirm-button
    x-show="checked"
    :is-open="$isOpen"
    :route="route('collaborate.work-expressions.references.actions', ['expression' => $expressionId])"
    method="POST"
    :label="__('corpus.reference.' . $labelSuffix . $checkSuffix)"
    :confirmation="__('corpus.reference.' .  $labelSuffix . $checkSuffix .  '_confirmation')"
    styling="outline"
    :button-theme="$buttonTheme ?? null"
    @click="checked = {{ json_encode(!$checkFirst) }};open = true;"
    {{ $attributes }}
  >
      <x-slot:formBody>
        <input type="hidden" name="action" value="{{ $action }}" />
        <template x-for="(val,selRef) in selectedRefs" :key="selRef">
          <input type="hidden" x-bind:name="'reference_' + selRef" x-bind:value="val">
        </template>
      </x-slot:formBody>

      <span>{{ __('corpus.reference.' . $labelSuffix) }}</span>
    </x-ui.confirm-button>

</div>
