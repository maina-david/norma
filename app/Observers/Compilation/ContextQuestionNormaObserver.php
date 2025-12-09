<?php

namespace App\Observers\Compilation;

use App\Enums\Compilation\ApplicabilityActivityType;
use App\Models\Auth\User;
use App\Models\Compilation\ApplicabilityActivity;
use App\Models\Customer\Pivots\ContextQuestionNorma;
use Illuminate\Support\Facades\Auth;

class ContextQuestionNormaObserver
{
    /**
     * Listen to the updated event.
     *
     * @param \App\Models\Customer\Pivots\ContextQuestionNorma $answer
     *
     * @return void
     */
    public function saved(ContextQuestionNorma $answer): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        if ($answer->isDirty(['answer']) && $user) {
            ApplicabilityActivity::create([
                'activity_type' => ApplicabilityActivityType::ANSWER_CHANGED,
                'previous' => $answer->getOriginal('answer'),
                'current' => $answer->answer,
                'user_id' => $user->id,
                'place_id' => $answer->place_id,
                'context_question_id' => $answer->context_question_id,
            ]);
        }
    }
}
