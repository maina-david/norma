<?php

namespace App\Http\Controllers\Workflows\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Workflows\BoardRequest;
use App\Http\ResourceActions\Workflows\ArchiveBoard;
use App\Http\ResourceActions\Workflows\DuplicateBoard;
use App\Http\ResourceActions\Workflows\RestoreBoard;
use App\Models\Workflows\Board;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BoardController extends CollaborateController
{
    protected string $sortBy = 'title';

    /**
     * {@inheritDoc}
     */
    protected static function resource(): string
    {
        return Board::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.boards';
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceFormRequest(): string
    {
        return BoardRequest::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function indexColumns(): array
    {
        return [
            'title' => fn ($row) => view('partials.workflows.collaborate.board.title-column', ['row' => $row]),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function baseQuery(Request $request): Builder
    {
        return parent::baseQuery($request)->with(['taskTypes', 'parentTaskType']);
    }

    /**
     * {@inheritDoc}
     */
    protected function createViewData(Request $request): array
    {
        return [
            'formWidth' => 'max-w-5xl',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function editViewData(Request $request): array
    {
        return $this->createViewData($request);
    }

    /**
     * {@inheritDoc}
     */
    protected function showViewData(Request $request): array
    {
        return [
            'formWidth' => 'max-w-3xl',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function postStore(Model $model, Request $request): void
    {
        /** @var Board $model */
        if ($request->has('task_types')) {
            $types = $request->get('task_types');

            /** @var Board $model */
            $model->taskTypes()->sync($types);
        }

        if ($request->get('for_legal_update', false)) {
            Board::where('for_legal_update', true)
                ->where('id', '!=', $model->id)
                ->update(['for_legal_update' => false]);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function postUpdate(Model $model, Request $request): void
    {
        $this->postStore($model, $request);
    }

    /**
     * {@inheritDoc}
     */
    protected static function resourceActions(): array
    {
        $actions = [];

        if (Auth::user()?->can('collaborate.workflows.board.archive')) {
            $actions[] = new ArchiveBoard();
            $actions[] = new RestoreBoard();
        }

        if (Auth::user()?->can('collaborate.workflows.board.duplicate')) {
            $actions[] = new DuplicateBoard();
        }

        return $actions;
    }
}
