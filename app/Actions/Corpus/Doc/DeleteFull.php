<?php

namespace App\Actions\Corpus\Doc;

use App\Models\Corpus\ContentResource;
use App\Models\Corpus\Doc;
use App\Stores\Corpus\ContentResourceStore;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Fully deletes a doc, including associated content resources from storage.
 */
class DeleteFull
{
    use AsAction;

    public function __construct(protected ContentResourceStore $contentResourceStore)
    {
    }

    public function handle(Doc $doc): void
    {
        $resourceIds = $this->gatherContentResources($doc);

        // will cascade delete associated toc items
        $doc->delete();
        /** @var Collection<ContentResource> */
        $resources = ContentResource::whereKey($resourceIds)
            ->whereDoesntHave('tocItems')
            ->whereDoesntHave('firstForDocs')
            ->whereDoesntHave('legalUpdates')
            ->whereDoesntHave('docMetasForSummary')
            ->get(['id', 'path', 'content_hash']);
        foreach ($resources as $resource) {
            $this->contentResourceStore->deleteResource($resource);
        }
    }

    /**
     * @param Doc $doc
     *
     * @return array<int>
     */
    protected function gatherContentResources(Doc $doc): array
    {
        $items = $doc->tocItems()->get(['id', 'content_resource_id']);
        $ids = $items->pluck('content_resource_id')->toArray();

        if ($doc->first_content_resource_id) {
            $ids[] = $doc->first_content_resource_id;
        }

        return array_unique($ids);
    }
}
