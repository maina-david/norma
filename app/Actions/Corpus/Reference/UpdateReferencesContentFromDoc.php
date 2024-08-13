<?php

namespace App\Actions\Corpus\Reference;

use App\Models\Auth\User;
use App\Models\Corpus\Doc;
use App\Models\Corpus\TocItem;
use App\Models\Corpus\Work;
use App\Services\Corpus\TocItemContentExtractor;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Checks all references' content and if there are changes to the text it will create a
 * reference content draft.
 */
class UpdateReferencesContentFromDoc
{
    use AsAction;

    public function __construct(protected TocItemContentExtractor $tocItemContentExtractor)
    {
    }

    /**
     * @codeCoverageIgnore
     *
     * @param int      $workId
     * @param int      $docId
     * @param int|null $userId
     *
     * @return void
     */
    public function asJob(int $workId, int $docId, ?int $userId = null): void
    {
        /** @var Work */
        $work = Work::find($workId);
        /** @var Doc */
        $doc = Doc::find($docId);
        /** @var User|null */
        $user = $userId ? User::find($userId) : null;
        $this->handle($work, $doc, $user);
    }

    /**
     * @param Work      $work
     * @param Doc       $doc
     * @param User|null $user
     *
     * @return void
     */
    public function handle(Work $work, Doc $doc, ?User $user = null): void
    {
        $work->references()
            ->doesntHave('contentDraft')
            ->with(['htmlContent', 'refPlainText'])
            ->chunk(50, function ($references) use ($doc, $user) {
                foreach ($references as $ref) {
                    if (is_null($ref->uid)) {
                        continue;
                    }
                    /** @var TocItem|null */
                    $tocItem = $doc->tocItems()->where('uid', $ref->uid)->first();

                    if (!$tocItem) {
                        continue;
                    }
                    $tocItemContent = $this->stripContent($this->tocItemContentExtractor->extractItem($tocItem));
                    $refContent = $this->stripContent($ref->htmlContent?->cached_content ?? '');
                    if ($tocItemContent !== $refContent || $ref->refPlainText?->plain_text !== trim($tocItem->label ?? '')) {
                        UpdateReferenceFromTocItem::dispatch($tocItem->id, $ref->id, $user?->id)
                            ->onQueue('caching');
                    }
                }
            });
    }

    protected function stripContent(string $content): string
    {
        $content = strip_tags($content);

        return (string) preg_replace('/\s+/', ' ', $content);
    }
}
