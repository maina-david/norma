<?php

namespace App\Actions\Corpus\Work;

use App\Actions\Corpus\Doc\LinkToWork;
use App\Enums\Corpus\WorkStatus;
use App\Models\Corpus\Doc;
use App\Models\Corpus\Work;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateFromDoc
{
    use AsAction;

    public int $jobUniqueFor = 120;

    public function __construct(
        protected LinkToWork $linkToWork,
    ) {
    }

    public function handle(Doc $doc): ?Work
    {
        // don't allow creating a new work for the same UID
        /** @var Work|null */
        $work = Work::whereNotNull('uid')->where('uid', $doc->uid)->first();
        if ($work) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        $doc->load('docMeta');

        /** @var Work */
        $work = Work::create([
            'uid' => $doc->uid,
            'title' => $doc->title,
            'source_unique_id' => $doc->source_unique_id,
            'title_translation' => $doc->docMeta->title_translation,
            'gazette_number' => $doc->docMeta->publication_number,
            'work_number' => $doc->docMeta->work_number,
            'notice_number' => $doc->docMeta->publication_document_number,
            'work_type' => $doc->docMeta->workType?->slug ?? 'act',
            'work_date' => $doc->docMeta->work_date,
            'language_code' => $doc->docMeta->language_code,
            'source_id' => $doc->source_id,
            'source_url' => $doc->docMeta->source_url,
            'active_doc_id' => $doc->id,
            'primary_location_id' => $doc->primary_location_id,
            // 'highlights' => $doc->docMeta->summary,
            'effective_date' => $doc->docMeta->effective_date,
            'status' => WorkStatus::pending()->value,
        ]);

        $this->linkToWork->handle($doc, $work);

        return $work;
    }

    /**
     * @codeCoverageIgnore
     *
     * @param Doc $doc
     *
     * @return string
     */
    public function getJobUniqueId(Doc $doc)
    {
        return static::class . '_' . $doc->id;
    }

    /**
     * @codeCoverageIgnore
     *
     * @param int $docId
     *
     * @return void
     */
    public function asJob(int $docId): void
    {
        /** @var Doc */
        $doc = Doc::findOrFail($docId);
        $this->handle($doc);
    }
}
