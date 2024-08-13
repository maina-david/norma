<?php

namespace App\Http\Controllers\Payments\Collaborate;

use App\Actions\Payments\GenerateInvoice;
use App\Actions\Payments\GenerateTransferWiseExport;
use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Models\Payments\Payment;
use App\Traits\UsesDateRangeFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class PaymentController extends CollaborateController
{
    use UsesDateRangeFilters;

    /** @var string */
    protected string $sortBy = 'id';

    protected string $sortDirection = 'desc';

    protected bool $withCreate = false;
    protected bool $withDelete = false;
    protected bool $withUpdate = false;
    protected bool $withShow = true;

    /**
     * {@inheritDoc}
     */
    protected static function resource(): string
    {
        return Payment::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.payments.payments';
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @codeCoverageIgnore
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return '';
    }

    /**
     * {@inheritDoc}
     */
    protected static function indexColumns(): array
    {
        return [
            'id' => fn ($row) => $row->id,
            'date_created' => fn ($row) => view('partials.ui.data-table.date-column', ['date' => $row->created_at])->render(),
            'team' => fn ($row) => static::renderPartial($row, 'team-column'),
            'amount' => fn ($row) => $row->target_amount . ' ' . $row->target_currency,
            'view' => fn ($row) => static::renderPartial($row, 'view-column'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function indexViewData(Request $request): array
    {
        return [
            'headings' => false,
            'view' => 'pages.payments.collaborate.payment.partials.index',
            'startDate' => $request->input('from'),
            'endDate' => $request->input('to'),
        ];
    }

    /**
     * Get the resource filters.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function resourceFilters(): array
    {
        return [
            'from' => $this->getFromFilter(),
            'to' => $this->getToFilter(),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function showViewData(Request $request): array
    {
        return [
            'formWidth' => 'w-full',
        ];
    }

    /**
     * Generate a new query builder instance for the resource.
     *
     * @param Request $request
     *
     * @return Builder
     */
    protected function baseQuery(Request $request): Builder
    {
        return Payment::with(['team']);
    }

    protected function showBaseQuery(Request $request): Builder
    {
        return Payment::with(['team', 'paymentRequests.task']);
    }

    public function downloadInvoice(GenerateInvoice $generateInvoice, Payment $payment): Response
    {
        $pdf = $generateInvoice->handle($payment);
        $invoiceDate = Carbon::parse($payment->created_at)->subDay();

        /** @var string */
        $name = sprintf('%s Invoice.pdf', $invoiceDate->format('F Y'));

        return $pdf->download($name);
    }

    public function generateCSV(Request $request): Response
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'date',
        ]);

        $startDate = Carbon::parse($request->input('start_date'));
        $endDate = Carbon::parse($request->input('end_date', now()->format('Y-m-d')));

        $rows = app(GenerateTransferWiseExport::class)->handle($startDate, $endDate);

        $csv = $rows->map(fn ($row) => implode(',', $row))->join("\n");

        /** @var Response */
        return response($csv, 200, [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="Payment CSV.csv"',
        ]);
    }
}
