<?php

namespace App\Models\Customer\Pivots;

use App\Models\Auth\User;
use App\Models\Customer\Norma;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @mixin IdeHelperContextQuestionNorma
 */
class ContextQuestionNorma extends Pivot
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
     * @param Norma  $norma
     *
     * @return Builder
     */
    public function scopeForNorma(Builder $builder, Norma $norma): Builder
    {
        return $builder->where('place_id', $norma->id);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function lastAnsweredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_answered_by');
    }
}
