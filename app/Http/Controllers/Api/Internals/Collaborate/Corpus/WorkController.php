<?php

namespace App\Http\Controllers\Api\Internals\Collaborate\Corpus;

use App\Http\Controllers\Api\AbstractApiController;
use App\Http\Resources\Internals\Collaborate\Corpus\WorkResource;
use App\Models\Corpus\Work;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class WorkController extends AbstractApiController
{
    /**
     * {@inheritDoc}
     */
    protected function getModelClass(): string
    {
        return Work::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function getApiResourceClass(): string
    {
        return WorkResource::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getQuery(Request $request): Builder
    {
        return parent::getQuery($request)->filter($request->only(['search', 'jurisdiction']));
    }
}
