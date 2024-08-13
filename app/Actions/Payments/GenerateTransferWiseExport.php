<?php

namespace App\Actions\Payments;

use App\Models\Payments\Payment;
use App\Services\Payments\TransferWiseClient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateTransferWiseExport
{
    use AsAction;

    public function __construct(protected TransferWiseClient $transferWiseClient)
    {
        // code...
    }

    /**
     * @param Carbon $startDate
     * @param Carbon $endDate
     *
     * @return Collection<array<mixed>>
     */
    public function handle(Carbon $startDate, Carbon $endDate): Collection
    {
        $startDateTxt = $startDate->format('dS M');
        $endDateTxt = $endDate->format('dS M Y');
        $description = "Payment for {$startDateTxt} to {$endDateTxt}";

        /** @var Collection<array<mixed>> */
        $rows = collect([
            [
                'recipientId', 'name', 'account', 'sourceCurrency', 'targetCurrency', 'amountCurrency', 'amount',
                'paymentReference', 'addressCountryCode', 'addressCity', 'addressFirstLine', 'addressState',
                'addressPostCode',
            ],
        ]);

        Payment::with(['team'])
            ->whereHas('team', function ($builder) {
                $builder->whereNotNull('currency')
                    ->whereNotNull('account_name')
                    ->where(function ($query) {
                        $query->whereNotNull('account_number')->orWhereNotNull('iban');
                    });
            })
            ->whereBetween('created_at', [$startDate->format('Y-m-d H:i:s'), $endDate->format('Y-m-d H:i:s')])
            ->chunk(300, function ($chunks) use ($description, $rows) {
                $chunks->each(function ($payment) use ($rows, $description) {
                    $team = $payment->team;
                    if (is_null($team)) {
                        // @codeCoverageIgnoreStart
                        return;
                        // @codeCoverageIgnoreEnd
                    }
                    $acc = trim((string) ($team->iban ?? $team->account_number));

                    /** @var string $currency */
                    $currency = $payment->target_currency;
                    /** @var float $targetAmount */
                    $targetAmount = $payment->target_amount;

                    $amount = $this->transferWiseClient->convert(
                        $currency,
                        $team->account_currency,
                        $targetAmount
                    );

                    $rows->push([
                        $team->transferwise_id,
                        $team->account_name,
                        "{$acc}",
                        'GBP',
                        trim($team->account_currency),
                        'target',
                        $amount,
                        $description,
                        trim($team->bank_country ?? ''),
                        '',
                        trim(str_replace("\n", ' ', str_replace(',', ';', $team->billing_address ?? ''))),
                        '',
                        '',
                    ]);
                });
            });

        return $rows;
    }
}
