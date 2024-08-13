<?php

namespace App\Http\Controllers\Api\Internals\Collaborate\Corpus;

use App\Http\Controllers\Api\AbstractApiController;
use App\Http\Resources\Internals\Collaborate\Corpus\CatalogueDocResource;
use App\Models\Corpus\CatalogueDoc;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class CatalogueDocController extends AbstractApiController
{
    /**
     * {@inheritDoc}
     */
    protected function getModelClass(): string
    {
        return CatalogueDoc::class;
    }

    /**
     * {@inheritDoc}
     */
    protected function getApiResourceClass(): string
    {
        return CatalogueDocResource::class;
    }

    /**
     * {@inheritDoc}
     */
    public function getQuery(Request $request): Builder
    {
        $search = $request->get('search');

        return parent::getQuery($request)
            ->when($search, function ($builder) use ($search) {
                $builder->where(function ($q) use ($search) {
                    $q->titleLike($search)
                        ->orWhere('title_translation', 'like', '%' . $search . '%')
                        ->orWhere('source_unique_id', 'like', '%' . $search . '%');
                });
            })
            ->filter($request->only(['jurisdiction']))
            ->with([
                'work:id,uid,title,title_translation',
                'latestDoc',
            ]);
    }
}
