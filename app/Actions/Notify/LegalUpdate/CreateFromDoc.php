<?php

namespace App\Actions\Notify\LegalUpdate;

use App\Models\Compilation\ContextQuestion;
use App\Models\Corpus\Doc;
use App\Models\Notify\LegalUpdate;
use Illuminate\Database\Eloquent\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateFromDoc
{
    use AsAction;

    public int $jobUniqueFor = 120;

    public function handle(Doc $doc): ?LegalUpdate
    {
        // don't allow creating a new doc if already created
        if (LegalUpdate::where('created_from_doc_id', $doc->id)->exists()) {
            // @codeCoverageIgnoreStart
            return null;
            // @codeCoverageIgnoreEnd
        }

        $doc->load(['docMeta', 'legalDomains']);

        $fullSummary = $doc->docMeta->summaryContentResource ? $doc->docMeta->summaryContentResource->getContentFromStorage() : null;

        /** @var LegalUpdate */
        $update = LegalUpdate::create([
            'created_from_doc_id' => $doc->id,
            'title' => $doc->title,
            'title_translation' => $doc->docMeta->title_translation,
            'publication_number' => $doc->docMeta->publication_number,
            'work_number' => $doc->docMeta->work_number,
            'publication_document_number' => $doc->docMeta->publication_document_number,
            'publication_date' => $doc->docMeta->work_date,
            'language_code' => $doc->docMeta->language_code,
            'source_id' => $doc->source_id,
            'primary_location_id' => $doc->primary_location_id,
            'highlights' => $fullSummary ? '<p>' . $fullSummary . '</p>' : null,
            'effective_date' => $doc->docMeta->effective_date,
            'content_resource_id' => $doc->first_content_resource_id,
        ]);

        $update->legalDomains()->attach($doc->legalDomains->pluck('id')->all());

        $this->attachContextQuestions($update, $doc);

        return $update;
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

    protected function attachContextQuestions(LegalUpdate $update, Doc $doc): void
    {
        $doc->load('categories.contextQuestions');

        /** @var Collection<ContextQuestion> */
        $questions = (new ContextQuestion())->newCollection();
        foreach ($doc->categories as $category) {
            foreach ($category->contextQuestions as $q) {
                if (!$questions->contains($q)) {
                    $questions->add($q);
                }
            }
        }

        foreach ($questions as $q) {
            $update->contextQuestions()->attach($q, ['source' => 'category_context_question']);
        }
    }
}
