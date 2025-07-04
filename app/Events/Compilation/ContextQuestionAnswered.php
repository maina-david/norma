<?php

namespace App\Events\Compilation;

use App\Enums\Compilation\ContextQuestionAnswer;
use App\Enums\Compilation\RecompilationActivityType;
use App\Models\Auth\User;
use App\Models\Compilation\ContextQuestion;
use App\Models\Customer\Norma;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContextQuestionAnswered extends RecompilationActivityEvent
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param Norma                $norma
     * @param User                  $user
     * @param ContextQuestion       $question
     * @param ContextQuestionAnswer $answer
     */
    public function __construct(
        public User $user,
        public Norma $norma,
        public ContextQuestion $question,
        public ContextQuestionAnswer $answer
    ) {
        parent::__construct($norma, $user);
    }

    /**
     * Get the activity type.
     *
     * @return RecompilationActivityType
     **/
    public function getActivityType(): RecompilationActivityType
    {
        return RecompilationActivityType::contextQuestionAnswered();
    }

    /**
     * Serialise the event to array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'context_question_id' => $this->question->id,
            'answer' => $this->answer->value,
        ];
    }
}
