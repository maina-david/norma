<?php

namespace App\Actions\Assess;

use App\Models\Customer\Norma;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateMetricsSnapshots
{
    use AsAction;

    public string $commandSignature = 'assess:create-metrics-snapshots';
    public string $commandDescription = 'Create snapshots of the assess metrics for all norma streams';

    public function handle(): void
    {
        // this should be run on/after the 1st of eaach month, and then it will generate a snapshot for the last month
        $monthEnd = now()->subMonth()->endOfMonth();
        Norma::active()->has('assessmentItemResponses')
            ->chunk(
                200,
                function ($normas) use ($monthEnd) {
                    foreach ($normas as $norma) {
                        // slow down slightly so we don't flood the queue with jobs;
                        usleep(20000);
                        CreateMetricsSnapshotForNorma::dispatch($norma->id, $monthEnd->format('Y-m-d H:i:s'));
                    }
                }
            );
    }

    /**
     * @codeCoverageIgnore
     *
     * @param Command $command
     *
     * @return void
     */
    public function asCommand(Command $command): void
    {
        $this->handle();
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
