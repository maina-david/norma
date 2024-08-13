<?php

namespace App\Stores\Compilation;

use App\Enums\Auth\UserActivityType;
use App\Enums\Compilation\ContextQuestionAnswer;
use App\Events\Auth\UserActivity\GenericActivity;
use App\Events\Compilation\ContextQuestionAnswered;
use App\Models\Auth\User;
use App\Models\Compilation\ContextQuestion;
use App\Models\Customer\Libryo;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class ContextQuestionLibryoStore
{
    /**
     * @param ContextQuestion       $question
     * @param Collection<Libryo>    $libryos
     * @param ContextQuestionAnswer $answer
     * @param User                  $answeredBy
     *
     * @return void
     */
    public function answerQuestionForLibryos(
        ContextQuestion $question,
        Collection $libryos,
        ContextQuestionAnswer $answer,
        User $answeredBy
    ): void {
        foreach ($libryos as $libryo) {
            $this->answerQuestionForLibryo($libryo, $question, $answer, $answeredBy);
        }
    }

    /**
     * @param Libryo                $libryo
     * @param ContextQuestion       $question
     * @param ContextQuestionAnswer $answer
     * @param User                  $answeredBy
     *
     * @return void
     */
    public function answerQuestionForLibryo(
        Libryo $libryo,
        ContextQuestion $question,
        ContextQuestionAnswer $answer,
        User $answeredBy
    ): void {
        try {
            $libryo->contextQuestions()->attach($question->id, [
                'answer' => $answer->value,
                'last_answered_by' => $answeredBy->id ?? null,
            ]);
        } catch (Exception $th) {
            $libryo->contextQuestions()->updateExistingPivot($question->id, [
                'answer' => $answer->value,
                'last_answered_by' => $answeredBy->id ?? null,
            ]);
        } finally {
            ContextQuestionAnswered::dispatch($answeredBy, $libryo, $question, $answer);
            event(new GenericActivity($answeredBy, UserActivityType::answeredApplicability(), null, $libryo));
        }
        $libryo->markAsNeedingRecompilation();
    }

    /**
     * Adds the context questions that haven't been attached yet. And removes those not present in the given list.
     * Default answer is "maybe" in DB, so no need to set pivot.
     *
     * @param Libryo     $libryo
     * @param array<int> $contextQuestionIds
     *
     * @return void
     */
    public function syncContextQuestions(Libryo $libryo, array $contextQuestionIds): void
    {
        $libryo->contextQuestions()->sync($contextQuestionIds);
    }
}
