<?php

namespace Tests\Unit\Services\Corpus;

use App\Jobs\Corpus\CacheWorkContent;
use App\Models\Corpus\Work;
use App\Models\System\ProcessingJob;
use App\Services\Corpus\VolumeHighlighter;
use App\Services\Storage\WorkStorageProcessor;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\Feature\My\MyTestCase;

class VolumeHighlighterTest extends MyTestCase
{
    public function testHighlight(): void
    {
        Bus::fake([
            CacheWorkContent::class,
        ]);

        $work = Work::factory()->hasExpressions(1)->create();
        $expression = $work->expressions->first();
        $work->update(['active_work_expression_id' => $expression->id]);
        Storage::fake('local-documents');
        $path = app(WorkStorageProcessor::class)->workPath($work);
        $path = "{$path}/cache/volume_1.html";
        $testContent = 'test content';

        Storage::disk('local-documents')->put($path, $testContent);

        $content = app(VolumeHighlighter::class)->highlight($expression, 1);
        $this->assertSame($testContent, $content);

        $expression->update(['volume' => 2]);

        //        $countJobs = ProcessingJob::count();

        app(VolumeHighlighter::class)->highlight($expression, 2);

        Bus::assertDispatched(CacheWorkContent::class);

        //        $this->assertGreaterThan($countJobs, ProcessingJob::count());

        $this->expectException(NotFoundHttpException::class);
        app(VolumeHighlighter::class)->highlight($expression, 3);
    }
}
