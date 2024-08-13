<div>
  @foreach ($getLines() as $line)
    <div>
      {{ $line }}
    </div>
  @endforeach

  @if ($getMessage())
    {{ $getMessage() }}
  @endif

  @if ($getTaskLink())
    <a class="text-primary" href="{{ $getTaskLink() }}">{{ __('workflows.task.show_title') }}</a>
  @endif
</div>
