<?php

namespace App\Models\Compilation;

use App\Enums\Assess\AssessActivityType;
use App\Enums\Compilation\ContextQuestionActivityType;
use App\Enums\Compilation\ContextQuestionAnswer;
use App\Models\AbstractModel;
use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @mixin IdeHelperContextQuestionActivity
 */
class ContextQuestionActivity extends AbstractModel
{
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

        switch ($this->activity_type) {
            case ContextQuestionActivityType::ANSWER_CHANGED->value:
                /** @var string $str */
                $str = __('assess.assessment_activity.changed_response_status', [
                    'name' => $name,
                    'from' => is_null($this->from_status) ? ContextQuestionAnswer::maybe()->label() : ContextQuestionAnswer::fromValue($this->from_status)->label(),
                    'to' => is_null($this->to_status) ? ContextQuestionAnswer::maybe()->label() : ContextQuestionAnswer::fromValue($this->to_status)->label(),
                ]);

                break;
            case AssessActivityType::comment()->value:
                /** @var string */
                $str = __('comments.made_comment', [
                    'name' => $name,
                ]);

                break;
            default:
                /** @var string */
                $str = '';

                break;
        }

        return $str;
    }

    /**
     * Check if the answer has changed.
     *
     * @return bool
     */
    public function isAnswerChanged(): bool
    {
        return ContextQuestionActivityType::ANSWER_CHANGED->value == $this->activity_type;
    }
}
