<?php

namespace App\Stores\Compilation;

use App\Enums\Auth\UserActivityType;
use App\Enums\Compilation\ContextQuestionAnswer;
use App\Events\Auth\UserActivity\GenericActivity;
use App\Events\Compilation\ContextQuestionAnswered;
use App\Models\Auth\User;
use App\Models\Compilation\ContextQuestion;
use App\Models\Customer\Norma;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class ContextQuestionNormaStore
{
    /**
     * @param ContextQuestion       $question
     * @param Collection<Norma>    $normas
     * @param ContextQuestionAnswer $answer
     * @param User                  $answeredBy
     *
     * @return void
     */
    public function answerQuestionForNormas(
        ContextQuestion $question,
        Collection $normas,
        ContextQuestionAnswer $answer,
        User $answeredBy
    ): void {
        foreach ($normas as $norma) {
            $this->answerQuestionForNorma($norma, $question, $answer, $answeredBy);
        }
    }

    /**
     * @param Norma                $norma
     * @param ContextQuestion       $question
     * @param ContextQuestionAnswer $answer
     * @param User                  $answeredBy
     *
     * @return void
     */
    public function answerQuestionForNorma(
        Norma $norma,
        ContextQuestion $question,
        ContextQuestionAnswer $answer,
        User $answeredBy
    ): void {
        try {
            $norma->contextQuestions()->attach($question->id, [
                'answer' => $answer->value,
                'last_answered_by' => $answeredBy->id ?? null,
            ]);
        } catch (Exception $th) {
            $norma->contextQuestions()->updateExistingPivot($question->id, [
                'answer' => $answer->value,
                'last_answered_by' => $answeredBy->id ?? null,
            ]);
        } finally {
            ContextQuestionAnswered::dispatch($answeredBy, $norma, $question, $answer);
            event(new GenericActivity($answeredBy, UserActivityType::answeredApplicability(), null, $norma));
        }
        $norma->markAsNeedingRecompilation();
    }

    /**
     * Adds the context questions that haven't been attached yet. And removes those not present in the given list.
     * Default answer is "maybe" in DB, so no need to set pivot.
     *
     * @param Norma     $norma
     * @param array<int> $contextQuestionIds
     *
     * @return void
     */
    public function syncContextQuestions(Norma $norma, array $contextQuestionIds): void
    {
        $norma->contextQuestions()->sync($contextQuestionIds);
    }
}
