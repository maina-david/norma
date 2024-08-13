<?php

namespace App\Actions\Corpus\Reference;

use App\Models\Auth\User;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContentDraft;
use App\Models\Corpus\TocItem;
use App\Services\Corpus\TocItemContentExtractor;
use App\Services\HTMLPurifierService;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateReferenceFromTocItem
{
    use AsAction;

    public function __construct(
        protected TocItemContentExtractor $tocItemContentExtractor,
        protected HTMLPurifierService $htmlPurifier
    ) {
    }

    /**
     * @param TocItem   $tocItem
     * @param Reference $reference
     * @param User|null $user
     *
     * @return ReferenceContentDraft
     */
    public function handle(TocItem $tocItem, Reference $reference, ?User $user = null): ReferenceContentDraft
    {
        $content = $this->tocItemContentExtractor->extractItem($tocItem);
        $content = $this->htmlPurifier->cleanSection($content);

        $reference->uid = $tocItem->uid;
        $reference->level = 1;
        $reference->save();

        /** @var ReferenceContentDraft */
        return ReferenceContentDraft::updateOrCreate(['reference_id' => $reference->id], [
            'reference_id' => $reference->id,
            'title' => is_null($tocItem->label) ? null : trim($tocItem->label),
            'html_content' => $content,
            'author_id' => $user?->id,
        ]);
    }

    /**
     * @codeCoverageIgnore
     *
     * @param int $tocItemId
     * @param int $referenceId
     *
     * @return void
     */
    public function asJob(int $tocItemId, int $referenceId): void
    {
        /** @var TocItem */
        $item = TocItem::find($tocItemId);
        /** @var Reference */
        $ref = Reference::find($referenceId);
        $this->handle($item, $ref);
    }
}
