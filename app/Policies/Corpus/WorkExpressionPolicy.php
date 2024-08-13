<?php

namespace App\Policies\Corpus;

use App\Models\Auth\User;
use App\Models\Corpus\WorkExpression;
use App\Models\Workflows\Task;
use Illuminate\Auth\Access\HandlesAuthorization;

class WorkExpressionPolicy
{
    use HandlesAuthorization;

    /**
     * Check if the task is valid for the given document.
     *
     * @param WorkExpression $expression
     * @param Task|null      $task
     *
     * @return bool
     */
    protected function validateTaskDocument(WorkExpression $expression, ?Task $task): bool
    {
        $task?->load(['document.expression']);

        return $task && ($task->document?->expression?->id === $expression->id || $task->document?->expression?->work_id === $expression->work_id);
    }

    /**
     * Determine whether the user can manage bespoke references in the expression.
     *
     * @param User           $user
     * @param WorkExpression $expression
     * @param Task|null      $task
     *
     * @return bool
     */
    public function bespokeReferences(User $user, WorkExpression $expression, ?Task $task = null): bool
    {
        $validDocument = $this->validateTaskDocument($expression, $task);

        if ($user->can('collaborate.corpus.work-expression.bespoke-without-task')) {
            return !$task || $validDocument;
        }

        // @codeCoverageIgnoreStart
        return $user->can('collaborate.corpus.work-expression.bespoke') && $validDocument && $user->can('view', $task);
        // @codeCoverageIgnoreEnd
    }

    /**
     * Determine whether the user can manage bespoke references in the expression.
     *
     * @param User           $user
     * @param WorkExpression $expression
     * @param Task|null      $task
     *
     * @return bool
     */
    public function identifyRequirements(User $user, WorkExpression $expression, ?Task $task = null): bool
    {
        // @codeCoverageIgnoreStart
        return $this->bespokeReferences($user, $expression, $task);
        // @codeCoverageIgnoreEnd
    }

    /**
     * Determine whether the user can annotate the expression.
     *
     * @param User           $user
     * @param WorkExpression $expression
     * @param Task|null      $task
     *
     * @return bool
     */
    public function annotate(User $user, WorkExpression $expression, ?Task $task = null): bool
    {
        $validDocument = $this->validateTaskDocument($expression, $task);

        if ($user->can('collaborate.corpus.work-expression.annotate-without-task')) {
            return !$task || $validDocument;
        }

        return $user->can('collaborate.corpus.work-expression.annotate')
            && $validDocument
            && $user->can('view', $task);
    }

    /**
     * Determine whether the user can edit the document for the expression.
     *
     * @param User           $user
     * @param WorkExpression $expression
     * @param Task|null      $task
     *
     * @return bool
     */
    public function editDocument(User $user, WorkExpression $expression, ?Task $task = null): bool
    {
        $validDocument = $this->validateTaskDocument($expression, $task);

        if ($user->can('collaborate.corpus.work-expression.edit-document-without-task')) {
            return !$task || $validDocument;
        }

        // @codeCoverageIgnoreStart
        return $user->can('collaborate.corpus.work-expression.edit-document')
            && $validDocument
            && $user->can('view', $task);
        // @codeCoverageIgnoreEnd
    }

    /**
     * Determine whether the user can edit the toc for the expression.
     *
     * @param User           $user
     * @param WorkExpression $expression
     * @param Task|null      $task
     *
     * @return bool
     */
    public function editToC(User $user, WorkExpression $expression, ?Task $task = null): bool
    {
        $validDocument = $this->validateTaskDocument($expression, $task);

        if ($user->can('collaborate.corpus.work-expression.edit-toc-without-task')) {
            return !$task || $validDocument;
        }

        // @codeCoverageIgnoreStart

        return $user->can('collaborate.corpus.work-expression.edit-toc')
            && $validDocument
            && $user->can('view', $task);
        // @codeCoverageIgnoreEnd
    }
}
