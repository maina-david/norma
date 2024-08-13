<?php

namespace App\Actions\Corpus\Reference;

use App\Models\Corpus\Pivots\ReferenceClosure;
use App\Models\Corpus\Reference;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateReferenceClosures
{
    use AsAction;

    public string $jobQueue = 'long-running';

    public function handle(int $workId): void
    {
        $closureTable = (new ReferenceClosure())->getTable();
        $referenceTable = (new Reference())->getTable();

        DB::statement('
            DELETE FROM `' . $closureTable . '` 
            WHERE EXISTS 
            (select 1 from `' . $referenceTable . '` where work_id = ? and (`id` = `ancestor` or `id` = `descendant`))
        ', [$workId]);

        Reference::where('work_id', $workId)
            ->chunk(100, function ($references) use ($closureTable, $referenceTable) {
                foreach ($references as $ref) {
                    DB::statement('
                        INSERT IGNORE INTO `' . $closureTable . '` (`ancestor`,`descendant`, `depth`)
                        WITH RECURSIVE ref_path (`id`, `parent_id`, `depth`) AS
                        (
                        SELECT `id`, `parent_id`, 0 as `depth`
                            FROM `' . $referenceTable . '`
                            WHERE `id` = ?
                                AND (`type` = 11 or `type` = 16)
                        UNION ALL
                        SELECT c.`id`, c.`parent_id`, cte.`depth` + 1
                            FROM ref_path AS cte
                            JOIN `' . $referenceTable . '` AS c
                            ON cte.`id` = c.`parent_id`
                        )
                        SELECT
                            ? as `ancestor`,
                            `id` as `descendant`,
                            `depth`
                        FROM ref_path;
                    ', [$ref->id, $ref->id]);
                }
            });
    }

    public function asJob(int $workId): void
    {
        $this->handle($workId);
    }
}
