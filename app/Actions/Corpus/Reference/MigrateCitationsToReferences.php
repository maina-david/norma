<?php

namespace App\Actions\Corpus\Reference;

use App\Models\Corpus\Reference;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @codeCoverageIgnore
 */
class MigrateCitationsToReferences
{
    use AsAction;

    public function handle(): void
    {
        Reference::whereNull('title')
            ->typeCitation()
            ->with('citation')
            ->chunk(500, function ($references) {
                DB::statement("
                    UPDATE corpus_references r, corpus_citations c
                    SET
                        r.title = CONCAT_WS(' ', TRIM(c.number), TRIM(c.heading)),
                        r.is_section = c.is_section,
                        r.position = c.position
                    WHERE
                        r.referenceable_id = c.id AND
                        referenceable_type = 'citations' AND
                        r.id IN (" . implode(',', $references->modelKeys()) . ');
                    ');
            });
    }

    public function asJob(): void
    {
        $this->handle();
    }
}
