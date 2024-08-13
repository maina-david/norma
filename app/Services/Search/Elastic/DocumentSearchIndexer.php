<?php

namespace App\Services\Search\Elastic;

use App\Jobs\Search\Elastic\SearchIndexWork;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\App;

/**
 * Service for creating indexes and adding documents to Elasticsearch indexes.
 **/
class DocumentSearchIndexer
{
    /**
     * @param Client                $client
     * @param DocumentSearchIndices $indices
     **/
    public function __construct(protected Client $client, protected DocumentSearchIndices $indices)
    {
    }

    /**
     * Add reference to the index.
     *
     * @param Collection<Reference> $references
     *
     * @return void
     */
    public function addReferences(Collection $references)
    {
        $references->load('tags', 'work', 'citation');

        $index = $this->indices->getReferencesCurrentIndexName();
        /** @var array<mixed> $chunk */
        foreach ($references->chunk(10) as $chunk) {
            $body = [];
            foreach ($chunk as $ref) {
                $titleField = $this->indices->getTitleField($ref->work->language_code ?? 'default');
                $workTitleField = $this->indices->getWorkTitleField($ref->work->language_code ?? 'default');
                $contentField = $this->indices->getContentField($ref->work->language_code ?? 'default');
                $body[] = [
                    'index' => [
                        '_index' => $index,
                        '_id' => $ref->id,
                    ],
                ];
                $body[] = [
                    $titleField => $ref->refPlainText?->plain_text,
                    $workTitleField => $ref->work->title,
                    $contentField => strip_tags($ref->htmlContent?->cached_content),
                    'status' => $ref->status,
                    'work_id' => $ref->work_id,
                    'language_code' => $ref->work->language_code,
                    'tags' => $ref->tags->implode('title', ' • '),
                ];
            }
            try {
                $this->client->bulk([
                    'index' => $index,
                    'body' => $body,
                ]);
            } catch (Exception $ex) {
            }
        }
    }

    /**
     * @param int $workId
     *
     * @return void
     */
    public function addByWork(int $workId)
    {
        Reference::whereRelation('htmlContent', fn ($q) => $q->whereNotNull('cached_content'))
            ->where('work_id', $workId)
            ->active()
            ->typeCitation()
            ->with(['tags', 'work', 'citation', 'refPlainText', 'htmlContent:reference_id,cached_content'])
            ->chunk(200, function ($references) {
                $this->addReferences($references);
            });
    }

    /**
     * Add the given doc to the given index.
     *
     * @param string       $index
     * @param int          $id
     * @param array<mixed> $doc
     *
     * @return void
     */
    public function addDocToIndex(string $index, int $id, array $doc)
    {
        $this->client->index([
            'index' => $index,
            'id' => $id,
            'body' => $doc,
        ]);
    }

    /**
     * Add the given doc to the given index.
     *
     * @param string $index
     * @param int    $id
     *
     * @return void
     */
    public function deleteDoc(string $index, int $id)
    {
        $this->client->delete([
            'index' => $index,
            'id' => $id,
        ]);
    }

    /**
     * Add the given doc to the given index.
     *
     * @param int $id
     *
     * @return void
     */
    public function deleteReference(int $id)
    {
        $this->client->delete([
            'index' => $this->indices->getReferencesCurrentIndexName(),
            'id' => $id,
        ]);
    }

    /**
     * !! Danger - does a full index of all works.
     *
     * @return void
     */
    public function fullIndex()
    {
        Work::active()
            ->whereHas('references', function ($q) {
                $q->whereRelation('htmlContent', fn ($q) => $q->whereNotNull('cached_content'))
                    ->active()
                    ->typeCitation();
            })
            ->select('id')
            ->orderBy('id')
            ->chunk(300, function ($works) {
                // slow it down a bit so we don't flood the queue and hammer
                // the elasticsearch service
                if (!App::environment('testing')) {
                    // @codeCoverageIgnoreStart
                    sleep(30);
                    // @codeCoverageIgnoreEnd
                }
                foreach ($works as $work) {
                    SearchIndexWork::dispatch($work->id)->onQueue('arachno');
                }
            });
    }
}
