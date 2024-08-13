<form class="grid grid-cols-3 w-full">
  <input
      wire:loading.remove
      wire:target="changeAnswer({{ $yesValue }})"
      @if($answer !== $yesValue)
        wire:click="changeAnswer({{ $yesValue }})"
      @endif
      class="libryo-radio text-primary focus:ring-primary cursor-pointer"
      type="radio"
      name="answer"
      value="{{ $yesValue }}"
      {{ $answer === $yesValue ? 'checked' : '' }}
  />

  <div class="h-4" wire:loading wire:target="changeAnswer({{ $yesValue }})">
    <x-ui.icon class="text-primary fa-spin" name="spinner" />
  </div>



  <input
      wire:loading.remove
      wire:target="changeAnswer({{ $noValue }})"
      @if($answer !== $noValue)
        wire:click="changeAnswer({{ $noValue }})"
      @endif
      class="libryo-radio text-primary focus:ring-primary cursor-pointer"
      type="radio"
      name="answer"
      value="{{ $noValue }}"
      {{ $answer === $noValue ? 'checked' : '' }}
  />

  <div class="h-4" wire:loading wire:target="changeAnswer({{ $noValue }})">
    <x-ui.icon class="text-primary fa-spin" name="spinner" />
  </div>



  <input
      wire:loading.remove
      wire:target="changeAnswer({{ $unansweredValue }})"
      @if($answer !== $unansweredValue)
        wire:click="changeAnswer({{ $unansweredValue }})"
      @endif
      class="libryo-radio text-primary focus:ring-primary cursor-pointer"
      type="radio"
      name="answer"
      value="{{ $unansweredValue }}"
      {{ $answer === $unansweredValue ? 'checked' : '' }}
  />

  <div class="h-4" wire:loading wire:target="changeAnswer({{ $unansweredValue }})">
    <x-ui.icon class="text-primary fa-spin" name="spinner" />
  </div>

</form>
