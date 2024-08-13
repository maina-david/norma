<?php

namespace App\Http\Controllers\Traits;

use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceText;
use App\Models\Corpus\Work;
use App\Models\Geonames\Location;
use Illuminate\Database\Eloquent\Builder;

trait SelectsSummarisedReferenceFields
{
    /**
     * Apply the scopes and joins to the reference query builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function applyReferenceSelector(Builder $builder): Builder
    {
        return $builder
            ->active()
            ->forActiveWork()
            ->join(get_table(ReferenceText::class), qualify_column(ReferenceText::class, 'reference_id'), qualify_column(Reference::class, 'id'))
            ->join(get_table(Work::class), qualify_column(Work::class, 'id'), qualify_column(Reference::class, 'work_id'))
            ->join(get_table(Location::class), qualify_column(Location::class, 'id'), qualify_column(Work::class, 'primary_location_id'))
            ->select([
                qualify_column(Reference::class, 'id'),
                qualify_column(Reference::class, 'work_id'),
                sprintf('%s as title', (new ReferenceText())->qualifyColumn('plain_text')),
                sprintf('%s as work_title', (new Work())->qualifyColumn('title')),
                qualify_column(Location::class, 'flag'),
            ]);
    }
}
