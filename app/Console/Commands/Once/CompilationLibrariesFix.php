<?php

namespace App\Console\Commands\Once;

use App\Enums\Compilation\ContextQuestionAnswer;
use App\Models\Customer\Norma;
use Illuminate\Console\Command;

/**
 * @codeCoverageIgnore
 */
class CompilationLibrariesFix extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'once:compilation-libraries-fix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix for auto compilation - only run once';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        Norma::autocompiled()
            ->chunk(5, function ($normas) {
                foreach ($normas as $norma) {
                    $norma->load([
                        'library' => fn () => null,
                        'library.contextQuestions' => fn () => null,
                        'contextQuestions' => fn () => null,
                        'recompilationActivities' => fn ($q) => $q->where('activity_type', 3),
                    ]);
                    echo '*****************************************************************************  ' . $norma->id . PHP_EOL;
                    foreach ($norma->contextQuestions as $cq) {
                        $activity = $norma->recompilationActivities->last(function ($act) use ($cq) {
                            return ($act->details['context_question_id'] ?? null) === $cq->id;
                        });
                        if (is_null($activity)) {
                            /** @var \App\Models\Compilation\Library $library */
                            $library = $norma->library;
                            $libQuestion = $library->contextQuestions->first(function ($question) use ($cq) {
                                return $question->id === $cq->id;
                            });

                            // @phpstan-ignore-next-line
                            if (is_null($libQuestion) && $cq->pivot->answer === ContextQuestionAnswer::yes()->value) {
                                echo 'updating answer, No lib question: ' . $cq->id . PHP_EOL;
                                $norma->contextQuestions()
                                    ->updateExistingPivot($cq->id, [
                                        'answer' => ContextQuestionAnswer::maybe()->value,
                                    ]);
                            }
                        } else {
                            $norma->contextQuestions()
                                ->updateExistingPivot($cq->id, [
                                    'last_answered_at' => $activity->created_at?->format('Y-m-d H:i:s'),
                                    'last_answered_by' => $activity->user_id,
                                ]);
                        }
                    }
                }
            });

        return 0;
    }
}
