<?php

namespace App\Actions\Payments;

use App\Models\Collaborators\Team;
use App\Models\Payments\Payment;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Generate a payment for a team's outstanding payment requests.
 */
class CreatePaymentsForAllTeams
{
    use AsAction;

    public function handle(): void
    {
        // find all outstanding payment requests before the end of the previous month (this is scheduled to be run at the beginning of a new month)
        $before = Carbon::now()->startOfMonth()->subDays(2)->endOfMonth();
        $teams = Team::has('outstandingPaymentRequests')
            ->get(['id', 'currency']);

        foreach ($teams as $team) {
            app(CreatePaymentForOutstanding::class)->handle($team, $before);
        }
    }

    /**
     * @codeCoverageIgnore
     *
     * @return void
     */
    public function asJob(): void
    {
        $this->handle();
    }
}
