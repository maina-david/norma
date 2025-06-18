<?php

namespace App\Actions\Assess\AssessmentItemResponse;

use App\Enums\Assess\ResponseStatus;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Assess\Pivots\AssessmentItemReference;
use App\Models\Customer\Pivots\NormaReference;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteResponsesWithoutReferences
{
    use AsAction;

    public string $commandSignature = 'assess:delete-responses-without-law';
    public string $commandDescription = 'Deletes all assessment item responses that do not have law associated with it in the stream. Happens when streams are refined.';

    public function handle(): void
    {
        // creating a custom query here is much more efficient than trying to use eloquent relations
        $placeRefTable = (new NormaReference())->getTable();
        $aiRefTable = (new AssessmentItemReference())->getTable();
        $aiResponseTable = (new AssessmentItemResponse())->getTable();
        $notAssessed = ResponseStatus::notAssessed()->value;
        $sql = "DELETE FROM `{$aiResponseTable}` r
        WHERE answer = {$notAssessed}
        AND last_answered_by IS NULL
        AND NOT EXISTS (
            SELECT 1 FROM `{$placeRefTable}` pr
            RIGHT JOIN {$aiRefTable} air ON air.reference_id = pr.reference_id
            WHERE pr.place_id = r.place_id and r.assessment_item_id = air.assessment_item_id
        );";

        DB::statement($sql);
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
