<?php

namespace Tests\Unit\Actions\Corpus\Reference;

use App\Actions\Corpus\Reference\RequestReferenceContentUpdate;
use App\Enums\Corpus\ReferenceType;
use App\Models\Corpus\Reference;
use Tests\TestCase;

class RequestReferenceContentUpdateTest extends TestCase
{
    public function testHandle(): void
    {
        /** @var Reference */
        $workRef = Reference::factory()->create(['type' => ReferenceType::work()->value]);
        /** @var Reference */
        $citationRef = Reference::factory()->create(['work_id' => $workRef->work_id, 'parent_id' => $workRef->id, 'position' => 1]);

        app(RequestReferenceContentUpdate::class)->handle($citationRef);
        $this->assertNotNull($citationRef->contentDraft);
        $this->assertSame(
            $citationRef->refPlainText?->plain_text,
            $citationRef->contentDraft?->title
        );
        $this->assertSame(
            $citationRef->htmlContent?->cached_content,
            $citationRef->contentDraft?->html_content
        );
    }
}
