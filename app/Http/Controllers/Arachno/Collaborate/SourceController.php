<?php

namespace App\Http\Controllers\Arachno\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Arachno\SourceRequest;
use App\Models\Arachno\Source;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SourceController extends CollaborateController
{
    /** @var string */
    protected string $sortBy = 'title';

    /**
     * Get the class to be used for the CRUD operations.
     *
     * @return string
     */
    protected static function resource(): string
    {
        return Source::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.sources';
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return SourceRequest::class;
    }

    /**
     * Get the model columns that should be displayed in the index table. Use the dot notation for relations.
     * e.g. ['profile.id', 'name'].
     *
     * @return array<string, mixed>
     */
    protected static function indexColumns(): array
    {
        return [
            'title' => fn ($row) => $row->title,
            'created_at' => fn ($row) => $row->created_at,
            'jurisdictions' => fn ($row) => self::renderPartial($row, 'jurisdiction-column'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function baseQuery(Request $request): Builder
    {
        if ($request->routeIs('collaborate.sources.show')) {
            return parent::baseQuery($request)->with(['owner', 'manager']);
        }

        return parent::baseQuery($request);
    }

    /**
     * {@inheritDoc}
     */
    protected function createViewData(Request $request): array
    {
        return [
            'formWidth' => 'max-w-7xl',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function editViewData(Request $request): array
    {
        return [
            'formWidth' => 'max-w-7xl',
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function showViewData(Request $request): array
    {
        return [
            'formWidth' => 'max-w-7xl',
        ];
    }

    /**
     * Search and return results.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function json(Request $request): JsonResponse
    {
        $data = Source::search($request->get('search'))
            ->orderBy('title')
            ->get()
            ->toArray();

        return response()->json($data);
    }
}
