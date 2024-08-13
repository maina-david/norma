<?php

namespace App\Models\Compilation\Pivots;

use App\Models\Compilation\ContextQuestion;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperContextQuestionReference
 */
class ContextQuestionReference extends Pivot
{
    protected $table = 'corpus_context_question_reference';

    /** @var array<string, string> */
    protected $casts = [
        'applied_at' => 'datetime',
    ];

    /**
     * @return BelongsTo
     */
    public function contextQuestion(): BelongsTo
    {
        return $this->belongsTo(ContextQuestion::class);
    }
}
