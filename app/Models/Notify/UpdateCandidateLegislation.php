<?php

namespace App\Models\Notify;

use App\Models\AbstractModel;
use App\Models\Corpus\CatalogueDoc;
use App\Models\Corpus\Work;
use App\Models\Notify\Pivots\NotificationHandoverUpdateCandidateLegislation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @mixin IdeHelperUpdateCandidateLegislation
 */
class UpdateCandidateLegislation extends AbstractModel
{
    /**
     * @return BelongsToMany
     */
    public function handovers(): BelongsToMany
    {
        return $this->belongsToMany(NotificationHandover::class, (new NotificationHandoverUpdateCandidateLegislation())->getTable())
            ->using(NotificationHandoverUpdateCandidateLegislation::class)
            ->withPivot(['id', 'status', 'meta', 'task_id']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function catalogueDoc(): BelongsTo
    {
        return $this->belongsTo(CatalogueDoc::class);
    }

    /**
     * Get the source ID to be used.
     *
     * @return string
     */
    public function getUsableSourceURL(): string
    {
        if ($this->work_id) {
            return route('collaborate.works.show', ['work' => $this->work_id]);
        }

        if ($this->catalogue_doc_id) {
            return route('collaborate.corpus.catalogue-docs.show', ['catalogueDoc' => $this->catalogue_doc_id]);
        }

        return $this->source_url ?? '';
    }
}
