@php use App\Enums\Workflows\PaymentStatus; @endphp

<x-ui.input
  type="select"
  name="payment_status"
  :options="PaymentStatus::forSelector(true)"
  @change="$dispatch('changed', $el.value)"
/>
