<?php

namespace App\Models\Customer\Pivots;

use App\Models\Auth\User;
use App\Models\Customer\Libryo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperContextQuestionLibryo
 */
class ContextQuestionLibryo extends Pivot
{
    /** @var string */
    protected $table = 'context_question_place';

    public $timestamps = false;

    /** @var array<string, string> */
    protected $casts = [
        'last_answered_at' => 'datetime',
    ];

    /**
     * @param Builder $builder
     * @param Libryo  $libryo
     *
     * @return Builder
     */
    public function scopeForLibryo(Builder $builder, Libryo $libryo): Builder
    {
        return $builder->where('place_id', $libryo->id);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lastAnsweredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_answered_by');
    }
}
