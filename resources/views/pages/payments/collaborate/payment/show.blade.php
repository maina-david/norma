<div class="flex justify-end">
  @can('downloadInvoice', $resource)
    <x-ui.button target="_blank" type="link" :href="route('collaborate.payments.payments.invoice.download', ['payment' => $resource->id])" theme="primary" class="tippy cursor-pointer"
                 data-tippy-content="{{ __('payments.payment.download_invoice') }}">
      <x-ui.icon name="print" />
    </x-ui.button>
  @endcan
</div>
<x-ui.show-field :label="__('payments.payment.date')">
  <x-ui.timestamp :timestamp="$resource->created_at" />
</x-ui.show-field>

@can('collaborate.collaborators.team.view')
  <x-ui.show-field :label="__('payments.payment.team')">
    <a class="text-primary cursor-pointer"
       href="{{ route('collaborate.teams.show', ['team' => $resource->team->id]) }}">{{ $resource->team->title }}</a>
  </x-ui.show-field>
@endcan

<x-ui.show-field :label="__('payments.payment.amount')"
                 :value="$resource->target_amount
                     ? $resource->target_amount . ' ' . $resource->target_currency
                     : '-'" />

<table class="min-w-full divide-y divide-norma-gray-300 mt-10">
  <thead class="bg-norma-gray-50">
    <tr>
      <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-norma-gray-900 sm:pl-6"></th>
      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-norma-gray-900">
        {{ __('payments.payment.amount') }}</th>
      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-norma-gray-900">
        {{ __('payments.payment.currency') }}</th>
      <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-norma-gray-900">
        {{ __('payments.payment_request.units') }}</th>
      <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6 text-right">{{ __('workflows.task.task') }}
      </th>
    </tr>
  </thead>
  <tbody class="divide-y divide-norma-gray-200 bg-white">
    @foreach ($resource->paymentRequests as $payRequest)
      <tr>
        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-norma-gray-900 sm:pl-6">{{ $payRequest->id }}
        </td>
        <td class="whitespace-nowrap px-3 py-4 text-sm text-norma-gray-500">{{ $payRequest->amount_paid }}</td>
        <td class="whitespace-nowrap px-3 py-4 text-sm text-norma-gray-500">{{ $payRequest->currency_paid }}</td>
        <td class="whitespace-nowrap px-3 py-4 text-sm text-norma-gray-500">{{ $payRequest->units }}</td>
        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
          <div class="whitespace-normal ">
            <a href="{{ route('collaborate.tasks.show', ['task' => $payRequest->task->id]) }}"
               class="text-primary cursor-pointer">{{ $payRequest->task->title }}</a>
          </div>
        </td>
      </tr>
    @endforeach

  </tbody>
</table>
