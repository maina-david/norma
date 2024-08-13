<?php

namespace Tests\Unit\Actions\Corpus\Reference;

use App\Actions\Corpus\Reference\UpdateReferenceClosures;
use App\Models\Corpus\Reference;
use App\Models\Corpus\Work;
use Tests\TestCase;

class UpdateReferenceClosuresTest extends TestCase
{
    public function testHandle(): void
    {
        /** @var Work */
        $work = Work::factory()->has(Reference::factory()->count(3))->create();
        /** @var Reference */
        $firstRef = $work->references->first();
        /** @var Reference */
        $secondRef = $work->references[1];
        $secondRef->update(['parent_id' => $firstRef->id]);
        /** @var Reference */
        $ref3 = $work->references[2];
        $ref3->update(['parent_id' => $secondRef->id]);

        app(UpdateReferenceClosures::class)->handle($work->id);
        $secondRef = $work->refresh()->load(['references.ancestors', 'references.ancestorsWithSelf'])->references[1];
        /** @var Reference */
        $ref3 = $work->references[2];
        $this->assertTrue($secondRef->ancestors->contains($firstRef));
        $this->assertTrue($ref3->ancestors->contains($firstRef));
        $this->assertTrue($ref3->ancestors->contains($secondRef));
        $this->assertTrue($ref3->ancestorsWithSelf->contains($ref3));
    }
}
