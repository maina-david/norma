<?php

namespace App\Actions\Assess\AssessmentItemResponse;

use App\Models\Assess\AssessmentItemResponse;
use App\Models\Assess\Pivots\AssessmentItemReference;
use App\Models\Customer\Libryo;
use App\Models\Customer\Pivots\LibryoReference;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateMissingResponses
{
    use AsAction;

    public string $commandSignature = 'assess:create-missing-responses';
    public string $commandDescription = 'Creates assessment item responses for all libryos where that libryo has not got responses for recommended assessment items';

    public function handle(): void
    {
        // creating a custom query here is much more efficient than trying to use eloquent relations
        $libryoTable = (new Libryo())->getTable();
        $placeRefTable = (new LibryoReference())->getTable();
        $aiRefTable = (new AssessmentItemReference())->getTable();
        $aiResponseTable = (new AssessmentItemResponse())->getTable();
        $rawSql = "EXISTS (
            SELECT 1 FROM `{$placeRefTable}`
            WHERE `{$placeRefTable}`.`place_id` = `{$libryoTable}`.`id`
            AND
            EXISTS (
                SELECT 1 FROM `{$aiRefTable}`
                WHERE `{$aiRefTable}`.`reference_id` = `{$placeRefTable}`.`reference_id`
                AND
                NOT EXISTS (
                    SELECT 1 FROM `{$aiResponseTable}`
                    WHERE `assessment_item_id` = `{$aiRefTable}`.`assessment_item_id`
                    AND `place_id` = `{$libryoTable}`.`id`
                    AND `{$aiResponseTable}`.`deleted_at` IS NULL
                )
            )
        )";
        $cursor = Libryo::active()
            ->has('assessmentItemResponses')
            ->whereRaw($rawSql)
            ->cursor();

        foreach ($cursor as $libryo) {
            CreateResponsesForLibryo::dispatch($libryo);
        }
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
