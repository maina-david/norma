<?php

namespace App\Console\Commands\Once;

use App\Models\Notify\Pivots\NotificationHandoverUpdateCandidateLegislation;
use App\Models\Notify\UpdateCandidate;
use App\Models\Notify\UpdateCandidateLegislation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class MigrateUpdateCandidatesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:migrate-update-candidates';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate the affected legislation and handover actions.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        DB::statement('set FOREIGN_KEY_CHECKS=0');
        try {
            UpdateCandidate::with('notificationHandovers')
                ->chunkById(200, fn ($items) => $items->each(fn ($item) => $this->migrate($item)));
            // @codeCoverageIgnoreStart
        } catch (Throwable $e) {
            $this->error($e->getMessage());
        }
        // @codeCoverageIgnoreEnd
        DB::statement('set FOREIGN_KEY_CHECKS=1');

        return 0;
    }

    /**
     * Migrate the data.
     *
     * @param \App\Models\Notify\UpdateCandidate $candidate
     *
     * @return void
     */
    public function migrate(UpdateCandidate $candidate): void
    {
        $this->info("Migrating {$candidate->doc_id}");
        $legislation = collect($candidate->affected_legislation);

        if ($candidate->notificationHandovers->isNotEmpty() && $legislation->isNotEmpty()) {
            $this->populateHandoverActions($candidate, $legislation->shift());
        }

        $this->bulkInsert($candidate, $legislation->all());

        $this->info("Completed migration of {$candidate->doc_id}");
    }

    /**
     * Populate the first legislation with the handover actions.
     *
     * @param \App\Models\Notify\UpdateCandidate $candidate
     * @param array<string, string>              $legislation
     *
     * @return void
     */
    protected function populateHandoverActions(UpdateCandidate $candidate, array $legislation): void
    {
        /** @var UpdateCandidateLegislation $created */
        $created = UpdateCandidateLegislation::create($this->mapToLegislation($candidate, $legislation));

        $toInsert = [];

        foreach ($candidate->notificationHandovers as $handover) {
            $toInsert[] = [
                'notification_handover_id' => $handover->id,
                'update_candidate_legislation_id' => $created->id,
                'status' => $handover->pivot->status, // @phpstan-ignore-line
                'meta' => json_encode($handover->pivot->meta), // @phpstan-ignore-line
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        NotificationHandoverUpdateCandidateLegislation::insert($toInsert);
    }

    /**
     * Bulk insert the legislation.
     *
     * @param \App\Models\Notify\UpdateCandidate $candidate
     * @param array<int, array<string, string>>  $legislation
     *
     * @return void
     */
    protected function bulkInsert(UpdateCandidate $candidate, array $legislation): void
    {
        $toInsert = [];

        foreach ($legislation as $item) {
            $toInsert[] = $this->mapToLegislation($candidate, $item);
        }

        UpdateCandidateLegislation::insert($toInsert);
    }

    /**
     * Create the insertable array.
     *
     * @param \App\Models\Notify\UpdateCandidate $candidate
     * @param array<string, mixed>               $legislation
     *
     * @return array<string, mixed>
     */
    protected function mapToLegislation(UpdateCandidate $candidate, array $legislation): array
    {
        return [
            'doc_id' => $candidate->doc_id,
            'type' => $legislation['type'],
            'source_url' => $legislation['source_url'],
            'title' => $legislation['title'],
            'handover_comments' => $candidate->handover_comment,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
