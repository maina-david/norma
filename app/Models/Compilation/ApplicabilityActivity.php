<?php

namespace App\Models\Compilation;

use App\Enums\Compilation\ApplicabilityActivityType;
use App\Enums\Compilation\ContextQuestionAnswer;
use App\Models\AbstractModel;
use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Customer\Norma;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperApplicabilityActivity
 */
class ApplicabilityActivity extends AbstractModel
{
    /** @var array<string, string> */
    protected $casts = [
        'activity_type' => ApplicabilityActivityType::class,
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function contextQuestion(): BelongsTo
    {
        return $this->belongsTo(ContextQuestion::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function norma(): BelongsTo
    {
        return $this->belongsTo(Norma::class, 'place_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function note(): BelongsTo
    {
        return $this->belongsTo(ApplicabilityNote::class, 'applicability_note_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reference(): BelongsTo
    {
        return $this->belongsTo(Reference::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Convert the activity to text.
     *
     * @return string
     **/
    public function toText(): string
    {
        /** @var string $unknown */
        $unknown = __('auth.user.unknown_user');
        $name = $this->user->full_name ?? $unknown;

        return match ($this->activity_type) {
            ApplicabilityActivityType::ANSWER_CHANGED => __('assess.assessment_activity.changed_response_status', [
                'name' => $name,
                'from' => is_null($this->previous) ? ContextQuestionAnswer::maybe()->label() : ContextQuestionAnswer::fromValue($this->previous)->label(),
                'to' => ContextQuestionAnswer::fromValue($this->current)->label(),
            ]),
            ApplicabilityActivityType::COMMENT => __('comments.made_comment', ['name' => $name]),
            default => '',
        };
    }

    /**
     * Check if the answer has changed.
     *
     * @return bool
     */
    public function isAnswerChanged(): bool
    {
        return ApplicabilityActivityType::ANSWER_CHANGED == $this->activity_type;
    }
}
