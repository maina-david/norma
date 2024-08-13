<?php

namespace App\Actions\Corpus\Reference;

use App\Models\Auth\User;
use App\Models\Corpus\ReferenceContent;
use App\Models\Corpus\ReferenceContentDraft;
use App\Models\Corpus\ReferenceContentVersion;
use App\Models\Corpus\ReferenceContentVersionHtml;
use App\Models\Corpus\ReferenceText;
use App\Models\Corpus\Work;
use Lorisleiva\Actions\Concerns\AsAction;

class ApplyAllReferenceContentDrafts
{
    use AsAction;

    /**
     * @param Work                 $work
     * @param User|null            $user
     * @param array<int, int>|null $referenceIds
     *
     * @return void
     */
    public function handle(Work $work, ?User $user = null, ?array $referenceIds = null): void
    {
        $work->references()
            ->has('contentDraft')
            ->with(['contentDraft', 'htmlContent', 'refPlainText'])
            ->when($referenceIds, fn ($query) => $query->whereKey($referenceIds))
            ->chunk(100, function ($references) {
                foreach ($references as $reference) {
                    /** @var ReferenceContentVersion */
                    $version = ReferenceContentVersion::create([
                        'reference_id' => $reference->id,
                        'title' => $reference->contentDraft?->title,
                        'author_id' => $reference->contentDraft?->author_id,
                    ]);
                    ReferenceContentVersionHtml::create([
                        'reference_content_version_id' => $version->id,
                        'html_content' => $reference->contentDraft?->html_content,
                    ]);

                    ReferenceContent::updateOrCreate(['reference_id' => $reference->id], [
                        'reference_id' => $reference->id,
                        'cached_content' => $reference->contentDraft?->html_content,
                    ]);
                    ReferenceText::updateOrCreate(['reference_id' => $reference->id], [
                        'reference_id' => $reference->id,
                        'plain_text' => $reference->contentDraft?->title,
                    ]);
                }

                ReferenceContentDraft::whereKey($references->modelKeys())
                    ->delete();
            });
    }
}
