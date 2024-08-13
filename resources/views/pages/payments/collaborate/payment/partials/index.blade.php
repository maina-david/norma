@if ($startDate)
  <div class="flex justify-end">
    <x-ui.form method="post" :action="route('collaborate.payments.payments.export')" data-turbo="false" target="_blank">
      <input type="hidden" name="start_date" value="{{ $startDate }}" />
      @if ($endDate)
        <input type="hidden" name="end_date" value="{{ $endDate }}" />
      @endif
      <x-ui.button type="submit" theme="primary" styling="outline">
        <x-ui.icon name="download" class="mr-2" />
        {{ __('actions.export') }}
      </x-ui.button>
    </x-ui.form>
  </div>
@endif

@include('pages.crud.index-table')
