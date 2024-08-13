<?php

namespace App\Actions\Compilation\Autocompilation;

use App\Enums\Compilation\ContextQuestionAnswer;
use App\Mail\Compilation\Autocompilation\FailedImportMail;
use App\Models\Auth\User;
use App\Models\Compilation\ContextQuestion;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Services\Compilation\AutoCompilationExcelParser;
use App\Stores\Compilation\ContextQuestionLibryoStore;
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
     * @param ContextQuestionLibryoStore $contextQuestionLibryoStore
     */
    public function __construct(
        protected AutoCompilationExcelParser $autoCompilationExcelParser,
        protected ContextQuestionLibryoStore $contextQuestionLibryoStore,
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

        /** @var Collection<Libryo> */
        $libryos = Libryo::whereKey(array_keys($answers))
            ->forOrganisation($organisation->id)
            ->active()
            ->get()
            ->keyBy('id');

        $progressCallback ??= function ($item) {};

        foreach ($answers as $libryoId => $questionIds) {
            $currentAnswer++;

            $progressCallback(round(($currentAnswer / $totalAnswers) * 100, 0));

            /** @var Libryo|null */
            $libryo = $libryos[$libryoId] ?? null;
            if (is_null($libryo)) {
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
                $this->contextQuestionLibryoStore->answerQuestionForLibryo($libryo, $question, $cqAnswer, $user);
            }
        }
    }
}
