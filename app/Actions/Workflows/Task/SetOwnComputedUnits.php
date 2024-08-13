<?php

namespace App\Actions\Workflows\Task;

use App\Contracts\Workflows\Task\TaskAction;
use App\Enums\Workflows\ComputedTaskAction;
use App\Enums\Workflows\TaskComplexity;
use App\Models\Corpus\Reference;
use App\Models\Corpus\WorkExpression;
use App\Models\Workflows\Task;
use Throwable;

class SetOwnComputedUnits implements TaskAction
{
    /**
     * Execute the task action.
     *
     * @param Task       $task
     * @param mixed|null $payload
     *
     * @return void
     */
    public function handle(Task $task, mixed $payload = null): void
    {
        $task->update(['units' => $this->getUnits($task, $payload)]);
    }

    /**
     * Get the units to be applied to the dependent task.
     *
     * @param Task  $task
     * @param mixed $payload
     *
     * @return float
     */
    protected function getUnits(Task $task, mixed $payload = null): float
    {
        return match (ComputedTaskAction::tryFrom($payload)) {
            ComputedTaskAction::NOT_AID_STATEMENTS_3MIN => $this->onNotAIdStatements($task, 3),
            ComputedTaskAction::NOT_AID_STATEMENTS_4MIN => $this->onNotAIdStatements($task, 4),
            ComputedTaskAction::STATEMENTS => $this->onStatements($task),
            ComputedTaskAction::SUMMARIES => $this->onSummary($task),
            ComputedTaskAction::SUMMARIES_AND_STATEMENTS => $this->onSummaryAndStatements($task),
            default => 0,
        };
    }

    /**
     * Get the default complexity per statement.
     *
     * @return float
     */
    protected function getStatementsComplexity(): float
    {
        return 2 / 60;
    }

    /**
     * Get the complexity of the given task.
     *
     * @param Task $task
     *
     * @return float
     */
    protected function getTaskComplexity(Task $task): float
    {
        if (!$task->complexity) {
            // @codeCoverageIgnoreStart
            return 0;
            // @codeCoverageIgnoreEnd
        }

        try {
            /** @var int $complexity */
            $complexity = $task->complexity;
            $complexity = TaskComplexity::fromValue($complexity);

            return $complexity->hours();
            // @codeCoverageIgnoreStart
        } catch (Throwable $e) {
            return 0;
            // @codeCoverageIgnoreEnd
        }
    }

    /**
     * Get the units from unique statement and summary count.
     *
     * @param Task $task
     *
     * @return float
     */
    protected function onSummaryAndStatements(Task $task): float
    {
        $statements = $this->countStatements($task) * $this->getStatementsComplexity();

        $complexity = $this->getTaskComplexity($task);

        if (!$complexity) {
            // @codeCoverageIgnoreStart
            return $statements;
            // @codeCoverageIgnoreEnd
        }

        $summary = $this->countSummaries($task) * $complexity;

        return $statements + $summary;
    }

    /**
     * Get the units from the statement count.
     *
     * @param Task $task
     * @param int  $complexity
     *
     * @return float
     */
    protected function onNotAIdStatements(Task $task, int $complexity): float
    {
        $callback = fn ($builder) => $builder->doesntHave('assessmentItems');

        return $this->countCompleteOrDraftStatements($task, $callback) * ($complexity / 60);
    }

    /**
     * Get the units from the statement count.
     *
     * @param Task $task
     *
     * @return float
     */
    protected function onStatements(Task $task): float
    {
        return $this->countStatements($task) * $this->getStatementsComplexity();
    }

    /**
     * Get the units from the summary count.
     *
     * @param Task $task
     *
     * @return float
     */
    protected function onSummary(Task $task): float
    {
        $complexity = $this->getTaskComplexity($task);

        if (!$complexity) {
            // @codeCoverageIgnoreStart
            return 0;
            // @codeCoverageIgnoreEnd
        }

        return $this->countSummaries($task) * $complexity;
    }

    /**
     * Get the citations that have legal statements.
     *
     * @param Task $task
     *
     * @return int
     */
    protected function countStatements(Task $task): int
    {
        $workQuery = WorkExpression::where('id', $task->document?->work_expression_id)->select(['work_id']);

        return Reference::typeCitation()
            ->whereIn('work_id', $workQuery)
            ->has('requirementDraft')
            ->count();
    }

    /**
     * Get the citations that have legal statements.
     *
     * @param Task          $task
     * @param callable|null $closure
     *
     * @return int
     */
    protected function countCompleteOrDraftStatements(Task $task, ?callable $closure = null): int
    {
        $workQuery = WorkExpression::where('id', $task->document?->work_expression_id)->select(['work_id']);

        $builder = Reference::typeCitation()
            ->whereIn('work_id', $workQuery)
            ->where(fn ($query) => $query->has('refRequirement')->orHas('requirementDraft'));

        $builder->when($closure, function ($builder) use ($closure) {
            /** @var callable $closure */
            call_user_func($closure, $builder);
        });

        return $builder->count();
    }

    /**
     * Get the citations that have summaries.
     *
     * @param Task $task
     *
     * @return int
     */
    protected function countSummaries(Task $task): int
    {
        $workQuery = WorkExpression::where('id', $task->document?->work_expression_id)->select(['work_id']);

        return Reference::typeCitation()
            ->whereIn('work_id', $workQuery)
            ->has('summaryDraft')
            ->count();
    }
}
