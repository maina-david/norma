<?php

namespace App\Actions\Corpus\TocItem;

use App\Models\Corpus\Doc;
use App\Models\Corpus\TocItem;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateTocItemDocPositions implements ShouldBeUnique
{
    use AsAction;

    public function __construct()
    {
    }

    public function handle(Doc $doc): void
    {
        $tocItemTable = (new TocItem())->getTable();
        $sql = "                        
        WITH RECURSIVE toc_path (`id`, path) AS
        (
          SELECT `id`, CAST(LPAD(CAST(`position` as CHAR(50)),4,'0') as CHAR(150)) as path
            FROM `{$tocItemTable}`
            WHERE `parent_id` IS NULL and `doc_id` = ?
          UNION ALL
          SELECT ti.`id`,CONCAT(tip.path, '-', CAST(LPAD(CAST(ti.`position` as CHAR(50)),4,'0') as CHAR(150)))
            FROM toc_path AS tip JOIN `{$tocItemTable}` AS ti
              ON tip.`id` = ti.`parent_id`
        )
        SELECT * FROM toc_path
        ORDER BY path;";

        $rows = DB::select($sql, [$doc->id]);

        foreach ($rows as $index => $row) {
            /** @var TocItem */
            $item = TocItem::find($row->id);
            $item->update(['doc_position' => $index + 1]);
        }
    }

    /**
     * @codeCoverageIgnore
     *
     * @param int $docId
     *
     * @return string
     */
    public function getJobUniqueId(int $docId): string
    {
        return static::class . $docId;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return void
     */
    public function asJob(int $docId): void
    {
        /** @var Doc */
        $doc = Doc::findOrFail($docId);
        $this->handle($doc);
    }
}
