<?php

namespace App\Http\Controllers\Arachno\Collaborate;

use App\Http\Controllers\Abstracts\Collaborate\CollaborateController;
use App\Http\Requests\Arachno\TagRequest;
use App\Models\Ontology\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TagController extends CollaborateController
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
        return Tag::class;
    }

    /**
     * Get base resource route which will be added the suffix actions.
     *
     * @return string
     */
    protected static function resourceRoute(): string
    {
        return 'collaborate.tags';
    }

    /**
     * Get form request to be used when validating the input.
     *
     * @return string
     */
    protected static function resourceFormRequest(): string
    {
        return TagRequest::class;
    }

    /**
     * {@inheritDoc}
     */
    protected static function indexColumns(): array
    {
        return [
            'id' => fn ($row) => $row->id,
            'title' => fn ($row) => $row->title,
            'works' => fn ($row) => static::renderLink(route('collaborate.works.index', ['assessmentItems' => [$row->id]]), __('corpus.work.works')),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function indexViewData(Request $request): array
    {
        return [
            'headings' => false,
        ];
    }

    /**
     * Get the listing of tags.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function indexJson(Request $request): JsonResponse
    {
        $data = Tag::orderBy('title')
            ->filter($request->only(['search']))
            ->get(['id', 'title'])
            ->toArray();

        return response()->json($data);
    }
}
