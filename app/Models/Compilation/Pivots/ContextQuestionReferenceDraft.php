<?php

namespace App\Models\Compilation\Pivots;

use App\Models\Corpus\Reference;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperContextQuestionReferenceDraft
 */
class ContextQuestionReferenceDraft extends Pivot
{
    protected $table = 'context_question_reference_draft';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reference(): BelongsTo
    {
        return $this->belongsTo(Reference::class);
    }
}
