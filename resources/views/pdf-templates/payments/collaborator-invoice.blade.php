<?php
    $dueDate = $invoiceDate->clone()->setDay(5)->addMonth();
    $addresses = explode("\n", $payment->team->billing_address);
    $amountPaid = 0;
?>
<style>
* {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    line-height: 1.5;
    font-size: 16px;
    margin: 0;
    padding: 0;
}

.bordered td, .bordered th {
    border-bottom: 1px solid #ececec;
}
</style>

<div style="margin:1em;position:relative;">
<table style="width:100%;">
  <tr>
    <td style="font-weight:900;font-size:2em;">{{ $payment->team->title }}</td>
    <td></td>
  </tr>
  @foreach($addresses as $address)
  <tr>
    <td>{{ $address }}</td>
    <td></td>
  </tr>
  @endforeach
</table>

<table style="width:100%;margin-top:1.5em;">
  <tr><td style="font-weight:900;font-size:1.5em;">Invoice</td></tr>
  <tr>
    <td style="font-weight:900;font-size:1em;">{{ $invoiceDate->format("d") }} {{ $invoiceDate->format("F Y") }}</td>
  </tr>
</table>

<div style="margin-top:1.5em;">
  <div style="float:left">
    <div style="font-weight:900;font-size:1.1em">Invoice To</div>
    <div>Norma Ltd</div>
    <div>100 Avebury Boulevard</div>
    <div>Milton Keynes</div>
    <div>MK9 1FH</div>
    <div>VAT Registration No. 260148134</div>
  </div>

  <div style="float:right">
    <div>&nbsp;</div>
    <div>Invoice No. {{ $payment->id }}</div>
    <div>Due on {{ $dueDate->format("d") }} {{ $dueDate->format("F Y") }}</div>
  </div>
  <div style="clear:both;"></div>
</div>

<table class="bordered" style="width:100%;margin-top:1em">
  <tr>
    <th style="font-weight:900;text-align:left;">Description</th>
    <th style="font-weight:900;text-align:right;">Units</th>
    <th style="font-weight:900;text-align:right;min-width:72px;">Unit Price</th>
    <th style="font-weight:900;text-align:right;">Amount</th>
  </tr>

  @foreach($payment->paymentRequests as $request)
  <?php $amountPaid += $request->amount_paid; ?>
  <tr>
    <td>Payment for Task #{{ $request->task->id }}</td>
    <td style="text-align:right;">{{ number_format($request->units, 4) }}</td>
    <td style="text-align:right;">{{ number_format($request->rate_per_unit, 4) }}</td>
    <td style="text-align:right;font-weight:900;">{{ number_format($request->amount_paid, 4) }}</td>
  </tr>
  @endforeach

  <tr><td colspan="4">&nbsp;</td></tr>
  <tr>
    <td colspan="4" style="text-align:right;font-weight:900;font-size:1.5em;border-bottom:none;">{{ $payment->target_currency }} {{ number_format($payment->target_amount ?? $amountPaid, 2) }}</td>
  </tr>
</table>

<table style="margin-top:2em;float:left">
  <tr>
    <td colspan="2" style="font-weight:900;font-size:1.1em;margin-left:10px;">Bank Details</td>
  </tr>
  <tr>
    <td style="min-width:160px">Account Name</td>
    <td>{{ $payment->team->account_name }}</td>
  </tr>
  <tr>
    <td>Account Number</td>
    <td>{{ $payment->team->account_number }}</td>
  </tr>
  <tr>
    <td>Account Currency</td>
    <td>{{ $payment->team->account_currency }}</td>
  </tr>
  <tr>
    <td>Bank</td>
    <td>{{ $payment->team->bank_name }}</td>
  </tr>
  <tr>
    <td>Branch Code</td>
    <td>{{ $payment->team->branch_code }}</td>
  </tr>
  @if($payment->team->swift_code)
  <tr>
    <td>Swift Code</td>
    <td>{{ $payment->team->swift_code }}</td>
  </tr>
  @endif
  @if($payment->team->iban)
  <tr>
    <td>IBAN</td>
    <td>{{ $payment->team->iban }}</td>
  </tr>
  @endif

  @if($payment->team->other_billing_instructions)
  <tr>
    <td>Other Instructions</td>
    <td>{{ $payment->team->other_billing_instructions }}</td>
  </tr>
  @endif
</table>

<div style="float:right;font-size:5em;font-weight:900;padding:0.2em 0.8em;border:1px solid #000;transform:rotate(325deg);margin-top:60px;margin-right:20px;letter-spacing:12px;">PAID</div>

</div>
