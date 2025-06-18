<?php

namespace App\Actions\Assess\AssessmentItemResponse;

use App\Models\Assess\AssessmentItemResponse;
use App\Models\Assess\Pivots\AssessmentItemReference;
use App\Models\Customer\Norma;
use App\Models\Customer\Pivots\NormaReference;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateMissingResponses
{
    use AsAction;

    public string $commandSignature = 'assess:create-missing-responses';
    public string $commandDescription = 'Creates assessment item responses for all normas where that norma has not got responses for recommended assessment items';

    public function handle(): void
    {
        // creating a custom query here is much more efficient than trying to use eloquent relations
        $normaTable = (new Norma())->getTable();
        $placeRefTable = (new NormaReference())->getTable();
        $aiRefTable = (new AssessmentItemReference())->getTable();
        $aiResponseTable = (new AssessmentItemResponse())->getTable();
        $rawSql = "EXISTS (
            SELECT 1 FROM `{$placeRefTable}`
            WHERE `{$placeRefTable}`.`place_id` = `{$normaTable}`.`id`
            AND
            EXISTS (
                SELECT 1 FROM `{$aiRefTable}`
                WHERE `{$aiRefTable}`.`reference_id` = `{$placeRefTable}`.`reference_id`
                AND
                NOT EXISTS (
                    SELECT 1 FROM `{$aiResponseTable}`
                    WHERE `assessment_item_id` = `{$aiRefTable}`.`assessment_item_id`
                    AND `place_id` = `{$normaTable}`.`id`
                    AND `{$aiResponseTable}`.`deleted_at` IS NULL
                )
            )
        )";
        $cursor = Norma::active()
            ->has('assessmentItemResponses')
            ->whereRaw($rawSql)
            ->cursor();

        foreach ($cursor as $norma) {
            CreateResponsesForNorma::dispatch($norma);
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
