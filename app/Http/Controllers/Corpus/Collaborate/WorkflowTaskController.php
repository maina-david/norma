<?php

namespace App\Http\Controllers\Corpus\Collaborate;

use App\Enums\Workflows\CollaborateSessionKey;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Corpus\WorkExpression;
use App\Models\Workflows\Task;
use HotwiredLaravel\TurboLaravel\Http\PendingTurboStreamResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class WorkflowTaskController extends Controller
{
    /**
     * Get the current task.
     *
     * @return Task|null
     */
    protected function task(): ?Task
    {
        /** @var Task|null */
        return session(CollaborateSessionKey::CURRENT_TASK->value);
    }

    /**
     * Switch the current task.
     *
     * @param Task|null $task
     *
     * @return Task|null
     */
    protected function switchTask(?Task $task): ?Task
    {
        session()->put(CollaborateSessionKey::CURRENT_TASK->value, $task);

        return $task;
    }

    /**
     * Set the filters.
     *
     * @param array<array-key, string> $visible
     *
     * @return array<array-key, string>
     */
    protected function setReferenceFilters(array $visible): array
    {
        session()->put(CollaborateSessionKey::CURRENT_REFERENCE_FILTERS->value, $visible);

        return $visible;
    }

    /**
     * Get the filters.
     *
     * @param array<string, mixed> $default
     *
     * @return Collection
     */
    protected function getReferenceFilters(array $default = []): Collection
    {
        return collect(session(CollaborateSessionKey::CURRENT_REFERENCE_FILTERS->value, $default));
    }

    /**
     * Set the visible meta.
     *
     * @param array<array-key, string> $visible
     *
     * @return array<array-key, string>
     */
    protected function setVisibleMeta(array $visible): array
    {
        session()->put(CollaborateSessionKey::CURRENT_VISIBLE_META->value, $visible);

        return $visible;
    }

    /**
     * Get the visible meta.
     *
     * @return Collection<string>
     */
    protected function getVisibleMeta(): Collection
    {
        return collect(session(CollaborateSessionKey::CURRENT_VISIBLE_META->value, []));
    }

    /**
     * Get the current expressions that are being previewed.
     *
     * @return Collection
     */
    protected function getPreviewing(): Collection
    {
        /** @var Collection */
        return session(CollaborateSessionKey::CURRENT_EXPRESSION_PREVIEW->value, collect());
    }

    /**
     * Check if the expression is being previewed.
     *
     * @param int $expressionId
     *
     * @return bool
     */
    protected function isPreviewing(int $expressionId): bool
    {
        return $this->getPreviewing()->contains($expressionId);
    }

    /**
     * Add expression to preview.
     *
     * @param int $expression
     *
     * @return Collection
     */
    protected function addPreviewing(int $expression): Collection
    {
        $preview = $this->getPreviewing();

        if (!$preview->contains($expression)) {
            $preview->push($expression);
        }

        session()->put(CollaborateSessionKey::CURRENT_EXPRESSION_PREVIEW->value, $preview);

        return $preview;
    }

    /**
     * Remove expression from preview.
     *
     * @param int $expression
     *
     * @return Collection
     */
    protected function removePreviewing(int $expression): Collection
    {
        $preview = $this->getPreviewing()->filter(fn ($item) => $item !== $expression);

        session()->put(CollaborateSessionKey::CURRENT_EXPRESSION_PREVIEW->value, $preview);

        return $preview;
    }

    /**
     * Get the query string from the request.
     *
     * @return array<string,string>
     */
    protected function requestQuery(): array
    {
        /** @var Request $request */
        $request = request();

        /** @var array<string,string> */
        return $request->query();
    }

    /**
     * Get the common header.
     *
     * @param Request        $request
     * @param WorkExpression $expression
     *
     * @return PendingTurboStreamResponse
     */
    public function header(Request $request, WorkExpression $expression): PendingTurboStreamResponse
    {
        $this->abortNotTurbo();
        $expression->load(['work']);

        $payload = [
            'expression' => $expression,
            'task' => null,
            'chainTasks' => collect(),
            'expressions' => collect([$expression]),
        ];

        if (!$this->isPreviewing($expression->id)) {
            /** @var User $user */
            $user = $request->user();
            $payload['task'] = $this->task();
            $payload['chainTasks'] = Task::siblings($user, $expression, $payload['task']);
            $payload['expressions'] = WorkExpression::where('work_id', $expression->work_id)
                ->orderBy('start_date')
                ->get(['id', 'start_date']);
        }

        return singleTurboStreamResponse($request->header('turbo-frame') ?? '')
            ->view('pages.corpus.collaborate.annotations.partials.header', $payload);
    }
}
