<?php

namespace Tests\Unit\Actions\Corpus\Reference;

use App\Actions\Corpus\Reference\ApplyAllReferenceContentDrafts;
use App\Actions\Corpus\Reference\RequestReferenceContentUpdate;
use App\Enums\Corpus\ReferenceType;
use App\Models\Corpus\Reference;
use App\Models\Corpus\ReferenceContent;
use Tests\TestCase;

class ApplyAllReferenceContentDraftsTest extends TestCase
{
    public function testHandle(): void
    {
        /** @var Reference */
        $workRef = Reference::factory()->create(['type' => ReferenceType::work()->value]);
        /** @var Reference */
        $citationRef = Reference::factory()->create(['work_id' => $workRef->work_id, 'parent_id' => $workRef->id, 'position' => 1]);
        $content = 'Testing';
        ReferenceContent::updateOrCreate(['reference_id' => $citationRef->id], [
            'reference_id' => $citationRef->id,
            'cached_content' => $content,
        ]);
        app(RequestReferenceContentUpdate::class)->handle($citationRef);
        $this->assertNotNull($citationRef->contentDraft);
        $newContent = 'New content';
        $newTitle = 'New title';
        $citationRef->contentDraft?->update(['html_content' => $newContent, 'title' => $newTitle]);

        app(ApplyAllReferenceContentDrafts::class)->handle($workRef->work);
        $this->assertNull($citationRef->refresh()->contentDraft);

        $this->assertSame(
            $newTitle,
            $citationRef->refPlainText?->plain_text
        );
        $this->assertSame(
            $newContent,
            $citationRef->htmlContent?->cached_content,
        );
        $this->assertTrue($citationRef->contentVersions->count() === 1);
        $this->assertSame($newTitle, $citationRef->contentVersions->first()?->title);
        $this->assertSame($newContent, $citationRef->contentVersions->first()?->versionContentHtml?->html_content);
    }
}
