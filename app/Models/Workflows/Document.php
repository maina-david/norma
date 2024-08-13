<?php

namespace App\Models\Workflows;

use App\Models\AbstractModel;
use App\Models\Corpus\Work;
use App\Models\Corpus\WorkExpression;
use App\Models\Geonames\Location;
use App\Models\Notify\LegalUpdate;
use App\Models\Ontology\LegalDomain;
use App\Models\Workflows\Pivots\DocumentLegalDomain;
use App\Models\Workflows\Pivots\DocumentLocation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @mixin IdeHelperDocument
 */
class Document extends AbstractModel
{
    protected $table = 'librarian_documents';

    /**
     * @return BelongsTo
     */
    public function work(): BelongsTo
    {
        return $this->belongsTo(Work::class);
    }

    /**
     * @return BelongsTo
     */
    public function expression(): BelongsTo
    {
        return $this->belongsTo(WorkExpression::class, 'work_expression_id');
    }

    /**
     * @return BelongsTo
     */
    public function legalUpdate(): BelongsTo
    {
        return $this->belongsTo(LegalUpdate::class, 'legal_update_id');
    }

    /**
     * @return BelongsToMany
     */
    public function legalDomains(): BelongsToMany
    {
        return $this->belongsToMany(LegalDomain::class, (new DocumentLegalDomain())->getTable())
            ->using(DocumentLegalDomain::class);
    }

    /**
     * @return BelongsToMany
     */
    public function locations(): BelongsToMany
    {
        return $this->belongsToMany(Location::class, (new DocumentLocation())->getTable())
            ->using(DocumentLocation::class);
    }
}
