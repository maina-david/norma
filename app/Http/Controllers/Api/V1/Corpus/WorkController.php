<?php

namespace App\Http\Controllers\Api\V1\Corpus;

use App\Http\Controllers\Api\AbstractApiController;
use App\Http\Resources\Corpus\Work\V1\WorkResource;
use App\Models\Corpus\Work;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WorkController extends AbstractApiController
{
    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     **/
    protected function getModelClass(): string
    {
        return Work::class;
    }

    /**
     * @codeCoverageIgnore
     * {@inheritDoc}
     **/
    protected function getApiResourceClass(): string
    {
        return WorkResource::class;
    }

    /**
     * Get the allowed includes.
     *
     * @return array<string>
     */
    protected function getAllowedIncludes(): array
    {
        // @codeCoverageIgnoreStart
        return [
            'registerItems',
        ];
        // @codeCoverageIgnoreEnd
    }

    /**
     * Pre show hook.
     *
     * @param int $modelId
     *
     * @return void
     */
    protected function preShow(int $modelId): void
    {
        /** @var string $includes */
        $includes = app(Request::class)->query('include', '');

        if (Str::contains($includes, 'registerItems')) {
            $this->includes[] = 'registerItems.citation';
            $this->includes[] = 'registerItems.htmlContent';
            $this->includes[] = 'registerItems.refPlainText';
        }
    }
}
