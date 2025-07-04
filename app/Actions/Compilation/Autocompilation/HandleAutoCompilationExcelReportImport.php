<?php

namespace App\Actions\Compilation\Autocompilation;

use App\Enums\Compilation\ContextQuestionAnswer;
use App\Mail\Compilation\Autocompilation\FailedImportMail;
use App\Models\Auth\User;
use App\Models\Compilation\ContextQuestion;
use App\Models\Customer\Norma;
use App\Models\Customer\Organisation;
use App\Services\Compilation\AutoCompilationExcelParser;
use App\Stores\Compilation\ContextQuestionNormaStore;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use Lorisleiva\Actions\Concerns\AsAction;
use RuntimeException;

class HandleAutoCompilationExcelReportImport
{
    use AsAction;

    public string $jobQueue = 'compilation';

    /**
     * @param AutoCompilationExcelParser $autoCompilationExcelParser
     * @param ContextQuestionNormaStore $contextQuestionNormaStore
     */
    public function __construct(
        protected AutoCompilationExcelParser $autoCompilationExcelParser,
        protected ContextQuestionNormaStore $contextQuestionNormaStore,
    ) {
    }

    public function asJob(int $organisationId, string $filePath, int $userId): void
    {
        /** @var Organisation */
        $organisation = Organisation::findOrFail($organisationId);
        /** @var User $user */
        $user = User::find($userId);

        try {
            $this->handle($organisation, $filePath, $user);
            // @codeCoverageIgnoreStart
        } catch (RuntimeException $e) {
            Mail::to($user)->queue(new FailedImportMail($organisationId, $filePath));
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @param Organisation  $organisation
     * @param string        $filePath
     * @param User          $user
     * @param callable|null $progressCallback
     *
     * @return void
     */
    public function handle(Organisation $organisation, string $filePath, User $user, ?callable $progressCallback = null): void
    {
        $answers = $this->autoCompilationExcelParser->parse($filePath);
        $totalAnswers = count($answers);
        $currentAnswer = 0;

        /** @var Collection<Norma> */
        $normas = Norma::whereKey(array_keys($answers))
            ->forOrganisation($organisation->id)
            ->active()
            ->get()
            ->keyBy('id');

        $progressCallback ??= function ($item) {};

        foreach ($answers as $normaId => $questionIds) {
            $currentAnswer++;

            $progressCallback(round(($currentAnswer / $totalAnswers) * 100, 0));

            /** @var Norma|null */
            $norma = $normas[$normaId] ?? null;
            if (is_null($norma)) {
                continue;
            }
            /** @var Collection<ContextQuestion> */
            $questions = ContextQuestion::whereKey(array_keys($questionIds))->get()->keyBy('id');
            foreach ($questionIds as $qId => $answer) {
                if (!isset($questions[$qId])) {
                    continue;
                }
                try {
                    /** @var ContextQuestionAnswer */
                    $cqAnswer = ContextQuestionAnswer::fromValue($answer);
                    // @codeCoverageIgnoreStart
                } catch (InvalidArgumentException $th) {
                    continue;
                }
                // @codeCoverageIgnoreEnd

                /** @var ContextQuestion */
                $question = $questions[$qId];
                $this->contextQuestionNormaStore->answerQuestionForNorma($norma, $question, $cqAnswer, $user);
            }
        }
    }
}
